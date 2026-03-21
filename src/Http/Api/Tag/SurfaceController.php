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
        $runtime = [] !== $this->runtime ? $this->runtime : RuntimeSurfaceCatalog::read();

        return [
            'ok' => true,
            'service' => (string) ($runtime['service'] ?? 'tag'),
            'runtime' => (string) ($runtime['runtime'] ?? 'host-minimal'),
            'version' => (string) ($runtime['version'] ?? RuntimeVersion::read()),
            'surface' => is_array($runtime['route'] ?? null) ? $runtime['route'] : [],
            'examples' => is_array($runtime['example'] ?? null) ? $runtime['example'] : [],
            'docs' => is_array($runtime['doc'] ?? null) ? $runtime['doc'] : [],
            'public_surface' => is_array($runtime['public_surface'] ?? null) ? $runtime['public_surface'] : [],
        ];
    }
}
