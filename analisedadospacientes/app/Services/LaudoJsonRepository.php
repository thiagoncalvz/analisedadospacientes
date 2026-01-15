<?php

namespace App\Services;

use Illuminate\Support\Collection;

class LaudoJsonRepository
{
    public function all(): Collection
    {
        $paths = [
            storage_path('app/dadospacientes.json'),
            storage_path('app/private/dadospacientes.json'),
        ];

        $content = null;
        foreach ($paths as $path) {
            if (is_file($path)) {
                $content = file_get_contents($path);
                break;
            }
        }

        if ($content === null) {
            return collect();
        }

        $data = json_decode($content, true);

        if (!is_array($data)) {
            return collect();
        }

        if (array_key_exists('data', $data) && is_array($data['data'])) {
            $data = $data['data'];
        }

        if (array_key_exists('items', $data) && is_array($data['items'])) {
            $data = $data['items'];
        }

        return collect($data);
    }
}
