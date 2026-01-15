<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class LaudoJsonRepository
{
    public function all(): Collection
    {
        $path = 'dadospacientes.json';

        if (!Storage::exists($path)) {
            return collect();
        }

        $data = Storage::json($path);

        if (!is_array($data)) {
            return collect();
        }

        return collect($data);
    }
}
