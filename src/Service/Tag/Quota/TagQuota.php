<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Service\Tag\Quota;

use App\Service\Tag\Metric\TagMetrics;

final class TagQuota
{
    private bool $enabled;
    private int $read;
    private int $write;
    private int $burst;
    /** @var array<string, array<string, array{count:int, window:int}>> */
    private array $state = []; // [actor][op]={count,window}

    public function __construct(array $cfg){
        $this->enabled = (bool)($cfg['enabled'] ?? true);
        $this->read = (int)($cfg['per_minute_read'] ?? 300);
        $this->write = (int)($cfg['per_minute_write'] ?? 60);
        $this->burst = max(1, (int)($cfg['burst_factor'] ?? 2));
    }

    public function check(string $actor, string $op): void {
        if (!$this->enabled) return;
        $limit = $op === 'write' ? $this->write : $this->read;
        $limit *= $this->burst;
        $now = intdiv(time(), 60);
        if (!isset($this->state[$actor])) $this->state[$actor] = ['read'=>['count'=>0,'window'=>$now], 'write'=>['count'=>0,'window'=>$now]];
        if ($this->state[$actor][$op]['window'] !== $now) {
            $this->state[$actor][$op] = ['count'=>0,'window'=>$now];
        }
        $this->state[$actor][$op]['count'] += 1;
        if ($this->state[$actor][$op]['count'] > $limit) {
            TagMetrics::inc('tag_quota_denied_total', 1.0, ['actor'=>$actor,'op'=>$op]);
            throw new \RuntimeException('quota_exceeded'); // translate to 429 in HTTP layer
        }
    }
}
