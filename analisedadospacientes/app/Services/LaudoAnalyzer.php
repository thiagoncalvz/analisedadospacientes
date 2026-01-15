<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LaudoAnalyzer
{
    public function normalize(Collection $items): Collection
    {
        return $items->map(function ($item) {
            $item = is_array($item) ? $item : [];
            $pacienteData = $item['paciente'] ?? [];
            $pacienteEstruturado = $pacienteData['estruturado'] ?? (is_array($pacienteData) ? $pacienteData : []);
            $pacienteLiteral = $pacienteData['literal'] ?? [];

            $exameData = $item['exame'] ?? [];
            $exameEstruturado = $exameData['estruturado'] ?? (is_array($exameData) ? $exameData : []);
            $exameLiteral = $exameData['literal'] ?? [];

            $laudo = $item['laudo'] ?? [];
            $laudoLiteralCompleto = $item['laudo_literal_completo'] ?? [];

            $pecas = $this->normalizePecas($item);
            $diagnosticos = $this->collectDiagnosticos($item, $pecas);
            $atipia = $item['atipia'] ?? $this->collectStructuredAtypia($pecas);

            $item['paciente'] = [
                'nome' => $pacienteEstruturado['nome'] ?? $pacienteLiteral['nome'] ?? null,
                'idade' => $this->normalizeAge($pacienteEstruturado['idade'] ?? $pacienteLiteral['idade'] ?? null),
                'prontuario' => $pacienteEstruturado['prontuario'] ?? $pacienteLiteral['prontuario'] ?? null,
                'sexo' => $pacienteEstruturado['sexo'] ?? $pacienteEstruturado['genero'] ?? $pacienteLiteral['sexo'] ?? $pacienteLiteral['genero'] ?? null,
            ];

            $item['laudo'] = [
                'peca' => $laudoLiteralCompleto['numero_peca'] ?? $laudo['peca'] ?? null,
                'data' => $exameEstruturado['data_laudo'] ?? $exameLiteral['data'] ?? $laudo['data'] ?? null,
                'cid' => $exameEstruturado['cid'] ?? $exameLiteral['cid'] ?? $laudo['cid'] ?? null,
            ];

            $item['material'] = $item['material'] ?? $exameLiteral['material'] ?? $exameEstruturado['tipo'] ?? null;
            $item['localizacao'] = $item['localizacao'] ?? $exameLiteral['local'] ?? $exameLiteral['localizacao'] ?? null;
            $item['diagnosticos'] = $diagnosticos;
            $item['atipia'] = $atipia;
            $item['displasia'] = $item['displasia'] ?? null;
            $item['pecas_histologicas'] = $pecas;

            return $item;
        });
    }

    public function uniquePatients(Collection $items): Collection
    {
        $unique = [];

        foreach ($items as $item) {
            $paciente = $item['paciente'] ?? [];
            $prontuario = $paciente['prontuario'] ?? null;
            $nome = $paciente['nome'] ?? null;
            $key = $prontuario ?: $nome;

            if (!$key) {
                continue;
            }

            if (!array_key_exists($key, $unique)) {
                $unique[$key] = $item;
            }
        }

        return collect(array_values($unique));
    }

    public function statsBySex(Collection $items): array
    {
        $counts = [
            'Feminino' => 0,
            'Masculino' => 0,
            'Não informado' => 0,
        ];

        foreach ($items as $item) {
            if (!$this->isPolypProcedure($item)) {
                continue;
            }

            $sexo = $item['paciente']['sexo'] ?? null;
            $sexo = is_string($sexo) ? $this->normalizeText($sexo) : null;

            if ($sexo && str_contains($sexo, 'fem')) {
                $counts['Feminino']++;
            } elseif ($sexo && str_contains($sexo, 'masc')) {
                $counts['Masculino']++;
            } else {
                $counts['Não informado']++;
            }
        }

        $total = array_sum($counts);
        $percentages = [];
        foreach ($counts as $label => $count) {
            $percentages[$label] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        }

        return [
            'counts' => $counts,
            'percentages' => $percentages,
            'total' => $total,
            'missing' => $counts['Não informado'] > 0,
        ];
    }

    public function ageStats(Collection $items): array
    {
        $uniquePatients = $this->uniquePatients($items);
        $ages = [];

        foreach ($uniquePatients as $item) {
            $idade = $item['paciente']['idade'] ?? null;
            if (is_numeric($idade)) {
                $ages[] = (int) $idade;
            }
        }

        sort($ages);

        $count = count($ages);
        $uniqueCount = $uniquePatients->count();
        $average = $count > 0 ? round(array_sum($ages) / $count, 1) : null;
        $median = null;

        if ($count > 0) {
            $mid = (int) floor($count / 2);
            $median = $count % 2 ? $ages[$mid] : ($ages[$mid - 1] + $ages[$mid]) / 2;
        }

        $youngest = null;
        $oldest = null;

        foreach ($uniquePatients as $item) {
            $idade = $item['paciente']['idade'] ?? null;
            if (!is_numeric($idade)) {
                continue;
            }
            $idade = (int) $idade;
            if ($youngest === null || $idade < $youngest['idade']) {
                $youngest = [
                    'idade' => $idade,
                    'nome' => $item['paciente']['nome'] ?? 'Não informado',
                ];
            }
            if ($oldest === null || $idade > $oldest['idade']) {
                $oldest = [
                    'idade' => $idade,
                    'nome' => $item['paciente']['nome'] ?? 'Não informado',
                ];
            }
        }

        return [
            'average' => $average,
            'median' => $median,
            'count' => $uniqueCount,
            'youngest' => $youngest,
            'oldest' => $oldest,
        ];
    }

    public function histologyStats(Collection $items): array
    {
        $counts = [
            'Pólipo' => 0,
            'Inflamatório / Não neoplásico' => 0,
            'Adenocarcinoma / Câncer' => 0,
            'Indefinido' => 0,
        ];

        foreach ($items as $item) {
            $class = $this->classifyHistology($item);
            $counts[$class] = ($counts[$class] ?? 0) + 1;
        }

        $total = array_sum($counts);
        $lesionTotal = $counts['Pólipo'] + $counts['Inflamatório / Não neoplásico'] + $counts['Adenocarcinoma / Câncer'];

        $percentagesTotal = [];
        $percentagesLesion = [];

        foreach ($counts as $label => $count) {
            $percentagesTotal[$label] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            $percentagesLesion[$label] = $lesionTotal > 0 ? round(($count / $lesionTotal) * 100, 1) : 0;
        }

        return [
            'counts' => $counts,
            'percentages_total' => $percentagesTotal,
            'percentages_lesion' => $percentagesLesion,
            'total' => $total,
            'lesion_total' => $lesionTotal,
        ];
    }

    public function atypiaStats(Collection $items): array
    {
        $counts = [
            'Alto grau' => 0,
            'Moderado / Médio' => 0,
            'Baixo grau' => 0,
            'Ausente / Sem atipia' => 0,
            'Não informado' => 0,
        ];

        foreach ($items as $item) {
            $class = $this->classifyAtypia($item);
            $counts[$class] = ($counts[$class] ?? 0) + 1;
        }

        $total = array_sum($counts);
        $percentages = [];
        foreach ($counts as $label => $count) {
            $percentages[$label] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        }

        return [
            'counts' => $counts,
            'percentages' => $percentages,
            'total' => $total,
        ];
    }

    public function parsePolypSizeCategory(array $item): array
    {
        $structuredSizes = $this->extractStructuredPolypSizes($item);
        if (!empty($structuredSizes)) {
            $max = max($structuredSizes);
            $category = match (true) {
                $max >= 10 => '10 mm ou mais',
                $max > 5 => '5 a 9 mm',
                $max >= 2.1 => '2.1 até 5 mm',
                default => 'Menor que 2 mm',
            };

            return [
                'maior_eixo_mm' => $max,
                'categoria' => $category,
            ];
        }

        $strings = $this->extractStrings($item);
        $values = [];

        foreach ($strings as $text) {
            $normalized = $this->normalizeText($text);

            if (preg_match_all('/((?:\d+(?:[\.,]\d+)?\s*x\s*)+\d+(?:[\.,]\d+)?)(?:\s*)(mm|cm)\b/i', $normalized, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $unit = $match[2];
                    $numbers = preg_split('/x/i', $match[1]);
                    foreach ($numbers as $number) {
                        $values[] = $this->convertToMillimeters(trim($number), $unit);
                    }
                }
            }

            if (preg_match_all('/(\d+(?:[\.,]\d+)?)\s*(mm|cm)\b/i', $normalized, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $values[] = $this->convertToMillimeters($match[1], $match[2]);
                }
            }
        }

        $values = array_filter($values, fn ($value) => $value !== null);

        if (empty($values)) {
            return [
                'maior_eixo_mm' => null,
                'categoria' => 'Não informado',
            ];
        }

        $max = max($values);
        $category = match (true) {
            $max >= 10 => '10 mm ou mais',
            $max > 5 => '5 a 9 mm',
            $max >= 2.1 => '2.1 até 5 mm',
            default => 'Menor que 2 mm',
        };

        return [
            'maior_eixo_mm' => $max,
            'categoria' => $category,
        ];
    }

    public function classifyHistology(array $item): string
    {
        $pecas = $this->normalizePecas($item);
        foreach ($pecas as $peca) {
            $estruturado = $peca['estruturado'] ?? [];

            if (!empty($estruturado['adenocarcinoma'])) {
                return 'Adenocarcinoma / Câncer';
            }

            if (!empty($estruturado['neoplasia'])) {
                return 'Pólipo';
            }

            if (isset($estruturado['tipo_histologico']) && is_string($estruturado['tipo_histologico'])) {
                $tipo = $this->normalizeText($estruturado['tipo_histologico']);
                if (preg_match('/adenocarcinoma|carcinoma|neoplasia maligna|malign/iu', $tipo)) {
                    return 'Adenocarcinoma / Câncer';
                }
                if (preg_match('/polip|polipectomia|mucosectomia|adenoma|peca\s*polip/iu', $tipo)) {
                    return 'Pólipo';
                }
                if (preg_match('/gastrite|inflam|mucosa|tecido de granulacao|hiperplas|colite|ulcer/iu', $tipo)) {
                    return 'Inflamatório / Não neoplásico';
                }
            }
        }

        $text = $this->normalizeText($this->joinTextFields($item));

        if ($text === '') {
            return 'Indefinido';
        }

        if (preg_match('/adenocarcinoma|carcinoma|neoplasia maligna|malign/iu', $text)) {
            return 'Adenocarcinoma / Câncer';
        }

        if (preg_match('/polip|polipectomia|mucosectomia|adenoma|peca\s*polip/iu', $text)) {
            return 'Pólipo';
        }

        if (preg_match('/gastrite|inflam|mucosa|tecido de granulacao|hiperplas|colite|ulcer/iu', $text)) {
            return 'Inflamatório / Não neoplásico';
        }

        return 'Indefinido';
    }

    public function classifyAtypia(array $item): string
    {
        $pecas = $this->normalizePecas($item);
        $atipia = $item['atipia'] ?? $this->collectStructuredAtypia($pecas);
        $displasia = $item['displasia'] ?? null;
        $diagnostico = $this->joinTextFields($item);
        $text = $this->normalizeText(trim(implode(' ', array_filter([$atipia, $displasia, $diagnostico]))));

        if ($text === '') {
            return 'Não informado';
        }

        if (preg_match('/ausente|sem atipia/iu', $text)) {
            return 'Ausente / Sem atipia';
        }

        if (preg_match('/alto grau|pouco diferenciado|displasia de alto grau/iu', $text)) {
            return 'Alto grau';
        }

        if (preg_match('/moderada|moderadamente diferenciado|medio/iu', $text)) {
            return 'Moderado / Médio';
        }

        if (preg_match('/baixo grau|bem diferenciado|displasia leve|displasia de baixo grau|leve/iu', $text)) {
            return 'Baixo grau';
        }

        return 'Não informado';
    }

    private function isPolypProcedure(array $item): bool
    {
        $pecas = $this->normalizePecas($item);
        foreach ($pecas as $peca) {
            $estruturado = $peca['estruturado'] ?? [];
            if (!empty($estruturado['neoplasia'])) {
                return true;
            }
            if (isset($estruturado['tipo_histologico']) && is_string($estruturado['tipo_histologico'])) {
                $tipo = $this->normalizeText($estruturado['tipo_histologico']);
                if (preg_match('/polip|polipectomia|mucosectomia|adenoma/iu', $tipo)) {
                    return true;
                }
            }
        }

        $text = $this->normalizeText($this->joinTextFields($item));

        return (bool) preg_match('/polip|polipectomia|mucosectomia|adenoma/iu', $text);
    }

    private function joinTextFields(array $item): string
    {
        $strings = $this->extractStrings($item);

        return implode(' ', $strings);
    }

    private function extractStrings(mixed $value): array
    {
        $strings = [];

        if (is_array($value)) {
            foreach ($value as $child) {
                $strings = array_merge($strings, $this->extractStrings($child));
            }
        } elseif (is_string($value)) {
            $strings[] = $value;
        }

        return $strings;
    }

    private function normalizePecas(array $item): array
    {
        $pecas = $item['pecas_histologicas'] ?? [];

        return is_array($pecas) ? $pecas : [];
    }

    private function collectDiagnosticos(array $item, array $pecas): array
    {
        $diagnosticos = $item['diagnosticos'] ?? [];

        if (is_string($diagnosticos)) {
            $diagnosticos = [$diagnosticos];
        }
        if (!is_array($diagnosticos)) {
            $diagnosticos = [];
        }

        if (empty($diagnosticos) && !empty($item['diagnostico']) && is_string($item['diagnostico'])) {
            $diagnosticos = [$item['diagnostico']];
        }

        foreach ($pecas as $peca) {
            $literal = $peca['literal'] ?? [];
            foreach (['diagnostico', 'microscopia_conclusao', 'conclusao'] as $field) {
                if (!empty($literal[$field]) && is_string($literal[$field])) {
                    $diagnosticos[] = $literal[$field];
                }
            }
        }

        return array_values(array_unique(array_filter($diagnosticos)));
    }

    private function collectStructuredAtypia(array $pecas): ?string
    {
        $values = [];

        foreach ($pecas as $peca) {
            $estruturado = $peca['estruturado'] ?? [];
            if (!empty($estruturado['grau_atipia']) && is_string($estruturado['grau_atipia'])) {
                $values[] = $estruturado['grau_atipia'];
            }
        }

        if (empty($values)) {
            return null;
        }

        return implode(' / ', array_unique($values));
    }

    private function extractStructuredPolypSizes(array $item): array
    {
        $values = [];

        foreach ($this->normalizePecas($item) as $peca) {
            $estruturado = $peca['estruturado'] ?? [];
            $tamanho = $estruturado['tamanho_mm'] ?? [];
            $maiorEixo = $tamanho['maior_eixo'] ?? null;

            if (is_numeric($maiorEixo)) {
                $values[] = (float) $maiorEixo;
            }
        }

        return $values;
    }

    private function normalizeAge(mixed $idade): mixed
    {
        if (is_numeric($idade)) {
            return (int) $idade;
        }

        if (is_string($idade) && preg_match('/(\d{1,3})/', $idade, $matches)) {
            return (int) $matches[1];
        }

        return $idade;
    }

    private function normalizeText(string $text): string
    {
        return Str::of($text)->lower()->ascii()->toString();
    }

    private function convertToMillimeters(string $value, string $unit): ?float
    {
        $value = str_replace(',', '.', $value);

        if (!is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        return strtolower($unit) === 'cm' ? $number * 10 : $number;
    }
}
