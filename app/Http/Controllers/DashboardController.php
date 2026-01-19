<?php

namespace App\Http\Controllers;

use App\Services\LaudoAnalyzer;
use App\Services\LaudoJsonRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, LaudoJsonRepository $repository, LaudoAnalyzer $analyzer): View
    {
        $items = $analyzer->normalize($repository->all());

        $enriched = $items->map(function ($item) use ($analyzer) {
            $size = $analyzer->parsePolypSizeCategory($item);

            return array_merge($item, [
                'histology' => $analyzer->classifyHistology($item),
                'atypia_class' => $analyzer->classifyAtypia($item),
                'polyp_size' => $size,
            ]);
        });

        $page = (int) $request->get('page', 1);
        $perPage = 15;
        $paginated = new LengthAwarePaginator(
            $enriched->forPage($page, $perPage)->values(),
            $enriched->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $uniquePatients = $analyzer->uniquePatients($items);
        $ageStats = $analyzer->ageStats($items);
        $sexStats = $analyzer->statsBySex($items);
        $histologyStats = $analyzer->histologyStats($items);
        $atypiaStats = $analyzer->atypiaStats($items);
        $locationStats = $analyzer->locationStats($items);
        $polypCountStats = $analyzer->polypCountStats($items);

        $polypSizedCount = $enriched->filter(fn ($item) => $item['polyp_size']['maior_eixo_mm'] !== null)->count();

        return view('dashboard', [
            'items' => $paginated,
            'totalItems' => $items->count(),
            'uniquePatientsCount' => $uniquePatients->count(),
            'polypSizedCount' => $polypSizedCount,
            'ageStats' => $ageStats,
            'sexStats' => $sexStats,
            'histologyStats' => $histologyStats,
            'atypiaStats' => $atypiaStats,
            'locationStats' => $locationStats,
            'polypCountStats' => $polypCountStats,
        ]);
    }
}
