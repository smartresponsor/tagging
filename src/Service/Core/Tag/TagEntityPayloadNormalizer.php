<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core\Tag;

final class TagEntityPayloadNormalizer
{
    /**
     * @param array<string,mixed> $payload
     *
     * @return array{name:string,slug:string,locale:string,weight:int}
     */
    public function normalizeCreate(array $payload, callable $slugFactory): array
    {
        $name = trim((string) ($payload['name'] ?? ''));
        if ('' === $name) {
            throw new \InvalidArgumentException('validation_failed');
        }

        $slug = trim((string) ($payload['slug'] ?? ''));
        if ('' === $slug) {
            $slug = (string) $slugFactory($name);
        }

        $locale = trim((string) ($payload['locale'] ?? 'en'));
        if ('' === $locale) {
            throw new \InvalidArgumentException('validation_failed');
        }

        $weight = $this->normalizeWeight($payload['weight'] ?? 0);

        return [
            'name' => $name,
            'slug' => $slug,
            'locale' => $locale,
            'weight' => $weight,
        ];
    }

    /**
     * @param array<string,mixed> $payload
     *
     * @return array{name?:string,locale?:string,weight?:int}
     */
    public function normalizePatch(array $payload): array
    {
        $patch = [];

        if (array_key_exists('name', $payload)) {
            $name = trim((string) $payload['name']);
            if ('' === $name) {
                throw new \InvalidArgumentException('validation_failed');
            }
            $patch['name'] = $name;
        }

        if (array_key_exists('locale', $payload)) {
            $locale = trim((string) $payload['locale']);
            if ('' === $locale) {
                throw new \InvalidArgumentException('validation_failed');
            }
            $patch['locale'] = $locale;
        }

        if (array_key_exists('weight', $payload)) {
            $patch['weight'] = $this->normalizeWeight($payload['weight']);
        }

        return $patch;
    }

    private function normalizeWeight(mixed $value): int
    {
        if (!is_int($value) && !(is_string($value) && is_numeric($value))) {
            throw new \InvalidArgumentException('validation_failed');
        }

        return (int) $value;
    }
}
