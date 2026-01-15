<?php

namespace App\Http\Controllers;

use App\Services\LaudoAnalyzer;
use App\Services\LaudoJsonRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends Controller
{
    public function index(Request $request, LaudoJsonRepository $repo, LaudoAnalyzer $analyzer)
    {
        $items = $analyzer->normalize($repo->all());

        $items = $items->map(function ($item) use ($analyzer) {
            $size = $analyzer->parsePolypSizeCategory($item);

            $item['classificacao_histologica'] = $analyzer->classifyHistology($item);
            $item['grau_atipia'] = $analyzer->classifyAtypia($item);
            $item['tamanho_polipo_mm'] = $size['maior_eixo_mm'];
            $item['categoria_tamanho'] = $size['categoria'];

            return $item;
        });

        $perPage = 25;
        $page = max(1, (int) $request->input('page', 1));
        $paginated = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $uniquePatients = $analyzer->uniquePatients($items);
        $sexStats = $analyzer->statsBySex($items);
        $ageStats = $analyzer->ageStats($items);
        $histologyStats = $analyzer->histologyStats($items);
        $atypiaStats = $analyzer->atypiaStats($items);

        $sizeKnown = $items->filter(fn ($item) => $item['tamanho_polipo_mm'] !== null)->count();

        return view('dashboard', [
            'items' => $paginated,
            'totalItems' => $items->count(),
            'uniquePatients' => $uniquePatients,
            'sexStats' => $sexStats,
            'ageStats' => $ageStats,
            'histologyStats' => $histologyStats,
            'atypiaStats' => $atypiaStats,
            'sizeKnown' => $sizeKnown,
        ]);
    }
}
