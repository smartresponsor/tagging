<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\Http\Tag;

final class StatusController
{
    public function status(): array
    {
        // Minimal health payload; in real app add DB/cache checks
        return [
            'ok' => true,
            'ts' => gmdate('c'),
            'service' => 'tag',
            'version' => 'rc3-pre-e25',
        ];
    }
}
