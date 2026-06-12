<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
// Minimal in-process metrics registry (Prometheus exposition).
// No external agent; use GET /tag/_metrics to scrape.

namespace App\Tagging\Service\Core\Metric;

final class TagMetrics
{
    /** @var array<string, float> */
    private static array $counters = [];
    /** @var array<string, array{count:int, sum:float}> */
    private static array $summaries = [];

    public static function inc(string $nameEntity, float $val = 1.0, array $labels = []): void
    {
        $key = self::key($nameEntity, $labels);
        self::$counters[$key] = (self::$counters[$key] ?? 0.0) + $val;
    }

    public static function observe(string $nameEntity, float $v, array $labels = []): void
    {
        $key = self::key($nameEntity, $labels);
        $s = self::$summaries[$key] ?? ['count' => 0, 'sum' => 0.0];
        ++$s['count'];
        $s['sum'] += $v;
        self::$summaries[$key] = $s;
    }

    public static function render(): string
    {
        $out = [];
        foreach (self::$counters as $k => $v) {
            [$nameEntity, $lbl] = self::split($k);
            $out[] = sprintf('%s%s %.6f', $nameEntity, $lbl, $v);
        }
        foreach (self::$summaries as $k => $s) {
            [$nameEntity, $lbl] = self::split($k);
            $out[] = sprintf('%s_count%s %d', $nameEntity, $lbl, $s['count']);
            $out[] = sprintf('%s_sum%s %.6f', $nameEntity, $lbl, $s['sum']);
        }

        return implode("\n", $out) . "\n";
    }

    private static function key(string $nameEntity, array $labels): string
    {
        if (!$labels) {
            return $nameEntity;
        }
        ksort($labels);
        $pairs = [];
        foreach ($labels as $k => $v) {
            $pairs[] = $k . '="' . str_replace('"', '\"', (string) $v) . '"';
        }

        return $nameEntity . '{' . implode(',', $pairs) . '}';
    }

    /**
     * @return array|string[]
     */
    private static function split(string $key): array
    {
        if (preg_match('/^([^{]+)(\{.*})$/', $key, $m)) {
            return [$m[1], $m[2]];
        }

        return [$key, ''];
    }
}
