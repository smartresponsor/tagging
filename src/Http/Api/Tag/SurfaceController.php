<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag;

final class SurfaceController
{
    public function __construct(private readonly array $runtime = [])
    {
    }

    /** @return array<string,mixed> */
    public function surface(): array
    {
        $catalog = require dirname(__DIR__, 4).'/config/tag_public_surface.php';
        $catalog = is_array($catalog) ? $catalog : [];
        $runtime = [] !== $this->runtime ? $this->runtime : RuntimeSurfaceCatalog::read();

        return [
            'ok' => true,
            'service' => (string) ($catalog['service'] ?? $runtime['service'] ?? 'tag'),
            'version' => RuntimeVersion::read(),
            'surface' => $catalog['route'] ?? [],
            'examples' => $catalog['example'] ?? [],
            'docs' => $catalog['doc'] ?? [],
            'public_surface' => $runtime['public_surface'] ?? [],
        ];
    }
}
