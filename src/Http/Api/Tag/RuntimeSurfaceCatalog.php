<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag;

final class RuntimeSurfaceCatalog
{
    /** @return array<string,mixed> */
    public static function read(): array
    {
        $cfg = require dirname(__DIR__, 4).'/config/tag_public_surface.php';

        return is_array($cfg) ? $cfg : [];
    }
}
