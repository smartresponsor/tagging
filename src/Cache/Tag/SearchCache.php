<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\Cache\Tag;

final class SearchCache
{
    public function __construct(private string $dir = 'var/cache/tag-search', private int $ttl = 60)
    {
        if (!is_dir($this->dir)) @mkdir($this->dir, 0777, true);
    }

    private function key(string $tenant, string $q, int $limit, int $offset): string
    {
        $norm = strtolower(trim($q));
        $hash = sha1($tenant.'|'.$norm.'|'.$limit.'|'.$offset);
        return $this->dir . DIRECTORY_SEPARATOR . $tenant . '__q_' . preg_replace('/[^a-z0-9]+/','-', $norm) . '__' . $hash . '.json';
    }

    /** @return array{hit:bool,data?:array<string,mixed>} */
    public function get(string $tenant, string $q, int $limit, int $offset): array
    {
        $file = $this->key($tenant, $q, $limit, $offset);
        if (!is_file($file)) return ['hit'=>false];
        if (filemtime($file) + $this->ttl < time()) { @unlink($file); return ['hit'=>false]; }
        $raw = file_get_contents($file);
        return ['hit'=>true, 'data'=> json_decode($raw ?: "{}", true) ?: []];
    }

    /** @param array<string,mixed> $data */
    public function set(string $tenant, string $q, int $limit, int $offset, array $data): void
    {
        $file = $this->key($tenant, $q, $limit, $offset);
        file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }
}
