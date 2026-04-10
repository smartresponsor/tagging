<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag;

final readonly class SurfaceController
{
    public function __construct(private array $runtime = [])
    {
    }

    /** @return array<string,mixed> */
    public function surface(): array
    {
        $runtime = [] !== $this->runtime ? $this->runtime : RuntimeSurfaceCatalog::read();

        return [
            'ok' => true,
            'service' => $this->runtimeString($runtime, 'service', 'tag'),
            'runtime' => $this->runtimeString($runtime, 'runtime', 'host-minimal'),
            'version' => $this->runtimeString($runtime, 'version', RuntimeVersion::read()),
            'surface' => $this->runtimeArray($runtime, 'route'),
            'examples' => $this->runtimeArray($runtime, 'example'),
            'docs' => $this->runtimeArray($runtime, 'doc'),
            'public_surface' => $this->runtimeArray($runtime, 'public_surface'),
        ];
    }

    /** @param array<string,mixed> $runtime */
    private function runtimeString(array $runtime, string $key, string $fallback): string
    {
        $value = $runtime[$key] ?? null;

        return is_string($value) && '' !== $value ? $value : $fallback;
    }

    /** @param array<string,mixed> $runtime
     * @return array<string,mixed>
     */
    private function runtimeArray(array $runtime, string $key): array
    {
        $value = $runtime[$key] ?? null;

        return is_array($value) ? $value : [];
    }
}
