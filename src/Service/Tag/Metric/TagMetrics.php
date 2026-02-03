<?php
declare(strict_types=1);
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
// Minimal in-process metrics registry (Prometheus exposition).
// No external agent; use GET /tag/_metrics to scrape.
namespace App\Service\Tag\Metric;

final class TagMetrics
{
    /** @var array<string, float> */
    private static array $counters = [];
    /** @var array<string, array{count:int, sum:float}> */
    private static array $summaries = [];

    public static function inc(string $name, float $val=1.0, array $labels=[]): void {
        $key = self::key($name, $labels);
        self::$counters[$key] = (self::$counters[$key] ?? 0.0) + $val;
    }

    public static function observe(string $name, float $v, array $labels=[]): void {
        $key = self::key($name, $labels);
        $s = self::$summaries[$key] ?? ['count'=>0,'sum'=>0.0];
        $s['count'] += 1; $s['sum'] += $v;
        self::$summaries[$key] = $s;
    }

    public static function render(): string {
        $out = [];
        foreach (self::$counters as $k=>$v) {
            [$name,$lbl] = self::split($k);
            $out[] = sprintf("%s%s %.6f", $name, $lbl, $v);
        }
        foreach (self::$summaries as $k=>$s) {
            [$name,$lbl] = self::split($k);
            $out[] = sprintf("%s_count%s %d", $name, $lbl, $s['count']);
            $out[] = sprintf("%s_sum%s %.6f", $name, $lbl, $s['sum']);
        }
        return implode("\n", $out) . "\n";
    }

    private static function key(string $name, array $labels): string {
        if (!$labels) return $name;
        ksort($labels);
        $pairs = [];
        foreach ($labels as $k=>$v) { $pairs[] = $k.'="'.str_replace('"','\"',(string)$v).'"'; }
        return $name . '{' . implode(',', $pairs) . '}';
    }
    private static function split(string $key): array {
        if (preg_match('/^([^{]+)(\{.*\})$/', $key, $m)) return [$m[1], $m[2]];
        return [$key, ''];
    }
}
