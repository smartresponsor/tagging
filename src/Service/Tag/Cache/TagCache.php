<?php
declare(strict_types=1);
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
namespace App\Service\Tag\Cache;

use App\Service\Tag\Metric\TagMetrics;

final class TagCache
{
    private int $ttl;
    private int $max;
    /** @var array<string, array{exp:int, val:mixed}> */
    private array $map = [];

    public function __construct(array $cfg){
        $this->ttl = (int)($cfg['ttl_seconds'] ?? 60);
        $this->max = (int)($cfg['max_items'] ?? 10000);
    }

    private function key(string $kind, array $params): string {
        ksort($params);
        return $kind.':'.sha1(json_encode($params, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
    }

    public function get(string $kind, array $params){
        $k = $this->key($kind, $params);
        $now = time();
        if (isset($this->map[$k]) && $this->map[$k]['exp'] >= $now) {
            TagMetrics::inc('tag_cache_hits_total', 1.0, ['kind'=>$kind]);
            return $this->map[$k]['val'];
        }
        TagMetrics::inc('tag_cache_misses_total', 1.0, ['kind'=>$kind]);
        return null;
    }

    public function set(string $kind, array $params, $val): void {
        if (count($this->map) > $this->max) { array_shift($this->map); }
        $k = $this->key($kind, $params);
        $this->map[$k] = ['exp'=> time() + $this->ttl, 'val'=>$val];
    }

    public function invalidateByTagId(string $tagId): void {
        foreach (array_keys($this->map) as $k) {
            if (str_contains($k, $tagId)) unset($this->map[$k]);
        }
    }

    public function clear(): void { $this->map = []; }
}
