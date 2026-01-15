<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class LaudoJsonRepository
{
    public function all(): Collection
    {
        if (!Storage::exists('dadospacientes.json')) {
            return collect();
        }

        $data = Storage::json('dadospacientes.json');

        if (!is_array($data)) {
            return collect();
        }

        return collect($data);
    }
}
