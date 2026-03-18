<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag;

final class SurfaceController
{
    public function __construct(private readonly array $runtime = [])
    {
    }

    public function surface(): array
    {
        return [
            'service' => (string) ($this->runtime['service'] ?? 'tag'),
            'version' => (string) ($this->runtime['version'] ?? 'dev'),
            'public_surface' => $this->runtime['public_surface'] ?? [],
        ];
    }
}
