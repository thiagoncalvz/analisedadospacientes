<?php

namespace App\Services;

use Illuminate\Support\Collection;

class LaudoAnalyzer
{
    public function normalize(Collection $items): Collection
    {
        return $items->map(function ($item) {
            $item = is_array($item) ? $item : [];

            $paciente = $item['paciente'] ?? [];
            $laudo = $item['laudo'] ?? [];

            $diagnosticos = $item['diagnosticos'] ?? null;
            if (is_string($diagnosticos)) {
                $diagnosticos = [$diagnosticos];
            }

            if (!is_array($diagnosticos)) {
                $diagnosticos = [];
            }

            $diagnostico = $item['diagnostico'] ?? null;
            if ($diagnostico && empty($diagnosticos)) {
                $diagnosticos = [$diagnostico];
            }

            $item['paciente'] = [
                'nome' => $paciente['nome'] ?? null,
                'idade' => $paciente['idade'] ?? null,
                'prontuario' => $paciente['prontuario'] ?? null,
                'sexo' => $paciente['sexo'] ?? null,
            ];

            $item['laudo'] = [
                'peca' => $laudo['peca'] ?? null,
                'data' => $laudo['data'] ?? null,
                'cid' => $laudo['cid'] ?? null,
            ];

            $item['material'] = $item['material'] ?? null;
            $item['localizacao'] = $item['localizacao'] ?? null;
            $item['diagnosticos'] = $diagnosticos;
            $item['atipia'] = $item['atipia'] ?? null;
            $item['displasia'] = $item['displasia'] ?? null;

            return $item;
        });
    }

    public function uniquePatients(Collection $items): Collection
    {
        return $items->mapWithKeys(function ($item) {
            $paciente = $item['paciente'] ?? [];
            $key = $paciente['prontuario']
                ? 'prontuario:' . $paciente['prontuario']
                : 'nome:' . ($paciente['nome'] ?? 'desconhecido');

            return [$key => [
                'nome' => $paciente['nome'] ?? 'Não informado',
                'idade' => $this->toNumber($paciente['idade'] ?? null),
            ]];
        })->values();
    }

    public function statsBySex(Collection $items): array
    {
        $polypItems = $items->filter(fn ($item) => $this->isPolypProcedure($item));

        $counts = $polypItems->countBy(function ($item) {
            $sexo = $item['paciente']['sexo'] ?? null;
            if (!$sexo) {
                return 'Não informado';
            }

            $normalized = $this->normalizeText($sexo);

            if (str_contains($normalized, 'fem')) {
                return 'Feminino';
            }

            if (str_contains($normalized, 'masc')) {
                return 'Masculino';
            }

            return 'Não informado';
        });

        $total = $polypItems->count();

        $rows = collect(['Feminino', 'Masculino', 'Não informado'])->map(function ($label) use ($counts, $total) {
            $count = $counts->get($label, 0);
            $percent = $total > 0 ? round(($count / $total) * 100, 1) : 0;

            return [
                'sexo' => $label,
                'quantidade' => $count,
                'percentual' => $percent,
            ];
        });

        return [
            'total' => $total,
            'rows' => $rows,
            'has_missing' => $counts->get('Não informado', 0) > 0,
        ];
    }

    public function ageStats(Collection $items): array
    {
        $unique = $this->uniquePatients($items);
        $ages = $unique->pluck('idade')->filter(fn ($age) => is_numeric($age))->values();

        $count = $ages->count();
        $mean = $count > 0 ? round($ages->avg(), 1) : null;
        $median = $count > 0 ? $this->median($ages->all()) : null;

        $minAge = $count > 0 ? $ages->min() : null;
        $maxAge = $count > 0 ? $ages->max() : null;

        $youngest = $minAge !== null
            ? $unique->firstWhere('idade', $minAge)
            : null;
        $oldest = $maxAge !== null
            ? $unique->firstWhere('idade', $maxAge)
            : null;

        return [
            'count' => $unique->count(),
            'mean' => $mean,
            'median' => $median,
            'min' => $minAge,
            'max' => $maxAge,
            'youngest' => $youngest,
            'oldest' => $oldest,
        ];
    }

    public function histologyStats(Collection $items): array
    {
        $rows = $items->map(function ($item) {
            return $this->classifyHistology($item);
        });

        $total = $rows->count();
        $polypRelated = $rows->filter(fn ($label) => $label !== 'INDEFINIDO');
        $polypTotal = $polypRelated->count();

        $labels = ['PÓLIPO', 'INFLAMATÓRIO', 'CÂNCER', 'INDEFINIDO'];
        $counts = $rows->countBy();

        $data = collect($labels)->map(function ($label) use ($counts, $total, $polypTotal) {
            $count = $counts->get($label, 0);
            $percentAll = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            $percentPolyp = $polypTotal > 0 ? round(($count / $polypTotal) * 100, 1) : 0;

            return [
                'label' => $label,
                'count' => $count,
                'percent_all' => $percentAll,
                'percent_polyp' => $percentPolyp,
            ];
        });

        return [
            'total' => $total,
            'total_polyp' => $polypTotal,
            'rows' => $data,
        ];
    }

    public function atypiaStats(Collection $items): array
    {
        $labels = ['Alto grau', 'Moderado / Médio', 'Baixo grau', 'Ausente / Sem atipia', 'Não informado'];

        $rows = $items->map(fn ($item) => $this->classifyAtypia($item));
        $total = $rows->count();
        $counts = $rows->countBy();

        $data = collect($labels)->map(function ($label) use ($counts, $total) {
            $count = $counts->get($label, 0);
            $percent = $total > 0 ? round(($count / $total) * 100, 1) : 0;

            return [
                'label' => $label,
                'count' => $count,
                'percent' => $percent,
            ];
        });

        return [
            'total' => $total,
            'rows' => $data,
        ];
    }

    public function parsePolypSizeCategory(array $item): array
    {
        $text = $this->collectText($item);

        $maxMm = $this->extractMaxMm($text);
        $category = 'Não informado';

        if ($maxMm !== null) {
            if ($maxMm >= 2.1 && $maxMm <= 5) {
                $category = '2.1 até 5 mm';
            } elseif ($maxMm > 5 && $maxMm < 10) {
                $category = '5 a 9 mm';
            } elseif ($maxMm >= 10) {
                $category = '10 mm ou mais';
            }
        }

        return [
            'maior_eixo_mm' => $maxMm,
            'categoria' => $category,
        ];
    }

    public function classifyHistology(array $item): string
    {
        $text = $this->normalizeText($this->collectText($item));

        $cancerPatterns = '/(adenocarcinoma|carcinoma|neoplasia maligna|tumor maligno)/i';
        if (preg_match($cancerPatterns, $text)) {
            return 'CÂNCER';
        }

        $polypPatterns = '/(polipo|polipectomia|adenoma|tubular|tubuloviloso|serrilhado|viloso|mucosectomia)/i';
        if (preg_match($polypPatterns, $text)) {
            return 'PÓLIPO';
        }

        $inflammatoryPatterns = '/(inflamator|gastrite|colite|granulacao|mucosa|hiperplas|tecido de granulacao|metaplasia)/i';
        if (preg_match($inflammatoryPatterns, $text)) {
            return 'INFLAMATÓRIO';
        }

        return 'INDEFINIDO';
    }

    public function classifyAtypia(array $item): string
    {
        $text = $this->normalizeText($this->collectText($item));
        $atipia = $this->normalizeText($item['atipia'] ?? '');
        $displasia = $this->normalizeText($item['displasia'] ?? '');

        if ($atipia && str_contains($atipia, 'ausente')) {
            return 'Ausente / Sem atipia';
        }

        $highPattern = '/(alto grau|pouco diferenciado|displasia de alto grau|alto)/i';
        if (preg_match($highPattern, $displasia . ' ' . $text)) {
            return 'Alto grau';
        }

        $mediumPattern = '/(moderada|moderadamente diferenciado|medio|m[eé]dio)/i';
        if (preg_match($mediumPattern, $displasia . ' ' . $text)) {
            return 'Moderado / Médio';
        }

        $lowPattern = '/(leve|baixo grau|bem diferenciado|displasia leve|displasia de baixo grau)/i';
        if (preg_match($lowPattern, $displasia . ' ' . $text)) {
            return 'Baixo grau';
        }

        return 'Não informado';
    }

    private function median(array $values): ?float
    {
        sort($values);
        $count = count($values);
        if ($count === 0) {
            return null;
        }

        $middle = intdiv($count, 2);
        if ($count % 2 === 0) {
            return round(($values[$middle - 1] + $values[$middle]) / 2, 1);
        }

        return (float) $values[$middle];
    }

    private function normalizeText(?string $text): string
    {
        $text = $text ?? '';
        $text = mb_strtolower($text);
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT', $text);

        return $converted !== false ? $converted : $text;
    }

    private function toNumber($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = str_replace(',', '.', (string) $value);

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    private function collectText(array $item): string
    {
        $strings = [];
        $iterator = function ($value) use (&$iterator, &$strings) {
            if (is_array($value)) {
                foreach ($value as $child) {
                    $iterator($child);
                }

                return;
            }

            if (is_string($value)) {
                $strings[] = $value;
            }
        };

        $iterator($item);

        return implode(' ', $strings);
    }

    private function extractMaxMm(string $text): ?float
    {
        $values = [];
        $lower = mb_strtolower($text);

        if (preg_match_all('/\d+(?:[.,]\d+)?\s*(?:x|×)\s*\d+(?:[.,]\d+)?(?:\s*(?:x|×)\s*\d+(?:[.,]\d+)?)+\s*mm\b/i', $lower, $matches)) {
            foreach ($matches[0] as $match) {
                if (preg_match_all('/\d+(?:[.,]\d+)?/', $match, $nums)) {
                    foreach ($nums[0] as $num) {
                        $values[] = $this->toNumber($num);
                    }
                }
            }
        }

        if (preg_match_all('/(\d+(?:[.,]\d+)?)\s*mm\b/i', $lower, $matches)) {
            foreach ($matches[1] as $match) {
                $values[] = $this->toNumber($match);
            }
        }

        if (preg_match_all('/(\d+(?:[.,]\d+)?)\s*cm\b/i', $lower, $matches)) {
            foreach ($matches[1] as $match) {
                $num = $this->toNumber($match);
                if ($num !== null) {
                    $values[] = $num * 10;
                }
            }
        }

        $values = array_filter($values, fn ($value) => $value !== null);

        return empty($values) ? null : max($values);
    }

    private function isPolypProcedure(array $item): bool
    {
        $text = $this->normalizeText($this->collectText($item));

        $polypKeywords = ['polipo', 'polipectomia', 'mucosectomia'];
        foreach ($polypKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return str_contains($text, 'biopsia') && str_contains($text, 'polipo');
    }
}
