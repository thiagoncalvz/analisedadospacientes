<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class LaudoJsonRepository
{
    public function all(): Collection
    {
        $path = 'dadospacientes.json';
        $disk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('app'),
        ]);

        if (!$disk->exists($path)) {
            return collect();
        }

        $data = $disk->json($path);

        if (!is_array($data)) {
            return collect();
        }

        return collect($data);
    }
}
