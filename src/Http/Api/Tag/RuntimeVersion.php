<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Http\Api\Tag;

final class RuntimeVersion
{
    public static function read(): string
    {
        $cfg = RuntimeSurfaceCatalog::read();
        $version = $cfg['version'] ?? 'dev';

        return is_string($version) && '' !== $version ? $version : 'dev';
    }
}
