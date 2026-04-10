<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Cache\Store\Tag;

final class TagFileCacheStore
{
    private const int JSON_FLAGS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    private string $dir;
    private int $ttl;

    public function __construct(string $dir, int $ttl = 60)
    {
        $this->ttl = $ttl;
        $this->dir = $this->resolveWritableDir($dir);
    }

    /** @param list<string|int> $segments */
    public function get(string $namespace, string $tenant, array $segments): array
    {
        $file = $this->key($namespace, $tenant, $segments);
        if (!is_file($file)) {
            return ['hit' => false];
        }

        $mtime = filemtime($file);
        if (false === $mtime || ($mtime + $this->ttl) < time()) {
            if (is_file($file)) {
                unlink($file);
            }

            return ['hit' => false];
        }

        return ['hit' => true, 'data' => $this->decodePayload(file_get_contents($file))];
    }

    /**
     * @param list<string|int>    $segments
     * @param array<string,mixed> $data
     *
     * @throws \JsonException
     */
    public function set(string $namespace, string $tenant, array $segments, array $data): void
    {
        $file = $this->key($namespace, $tenant, $segments);
        file_put_contents($file, json_encode($data, self::JSON_FLAGS | JSON_THROW_ON_ERROR));
    }

    public function clearTenant(string $namespace, string $tenant): void
    {
        $pattern = sprintf(
            '%s%s%s__%s__*.json',
            $this->dir,
            DIRECTORY_SEPARATOR,
            $this->safeSegment($namespace),
            $this->safeSegment($tenant),
        );

        foreach (glob($pattern) ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /** @param list<string|int> $segments */
    private function key(string $namespace, string $tenant, array $segments): string
    {
        $normalizedSegments = array_map(
            static fn (string|int $value): string => strtolower(trim((string) $value)),
            $segments,
        );

        $hash = sha1($namespace.'|'.$tenant.'|'.implode('|', $normalizedSegments));
        $slug = implode('__', array_map($this->safeSegment(...), $normalizedSegments));
        if ('' === $slug) {
            $slug = 'entry';
        }

        return sprintf(
            '%s%s%s__%s__%s__%s.json',
            $this->dir,
            DIRECTORY_SEPARATOR,
            $this->safeSegment($namespace),
            $this->safeSegment($tenant),
            $slug,
            $hash,
        );
    }

    private function safeSegment(string $value): string
    {
        $safe = preg_replace('/[^a-z0-9]+/', '-', $this->normalizeSegment($value)) ?? 'entry';

        return trim($safe, '-') ?: 'entry';
    }

    private function decodePayload(string|false $raw): array
    {
        if (false === $raw || '' === $raw) {
            return [];
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeSegment(string|int $value): string
    {
        return strtolower(trim((string) $value));
    }

    private function resolveWritableDir(string $preferredDir): string
    {
        if ($this->ensureWritableDirectory($preferredDir)) {
            return $preferredDir;
        }

        $fallbackDir = sprintf(
            '%s%s%s%s%s',
            rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR),
            DIRECTORY_SEPARATOR,
            'smartresponsor-tag-cache',
            DIRECTORY_SEPARATOR,
            trim(str_replace(['/', '\\'], '-', $preferredDir), '-'),
        );

        if ($this->ensureWritableDirectory($fallbackDir)) {
            return $fallbackDir;
        }

        return $preferredDir;
    }

    private function ensureWritableDirectory(string $dir): bool
    {
        if (!is_dir($dir) && !$this->createDirectory($dir) && !is_dir($dir)) {
            return false;
        }

        return is_writable($dir);
    }

    private function createDirectory(string $dir): bool
    {
        if (is_dir($dir)) {
            return true;
        }

        return mkdir($dir, 0777, true) || is_dir($dir);
    }
}
