<?php
declare(strict_types=1);
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
namespace App\Ops\Metrics;

final class TagMetrics
{
    private static ?PrometheusExporter $exp = null;

    public static function exporter(): PrometheusExporter {
        if (!self::$exp) {
            self::$exp = new PrometheusExporter();
            // Counters
            self::$exp->counter('tag_assign_total', 'Tag assignments', ['tenant']);
            self::$exp->counter('tag_unassign_total', 'Tag unassignments', ['tenant']);
            self::$exp->counter('tag_search_total', 'Tag search requests', ['tenant']);
            // Histogram (seconds): http_request_duration_seconds
            $buckets = [0.005,0.01,0.025,0.05,0.1,0.25,0.5,1.0,2.0,5.0];
            self::$exp->histogram('http_request_duration_seconds', 'HTTP request latency', $buckets, ['route']);
        }
        return self::$exp;
    }

    public static function incAssign(string $tenant): void { self::exporter()->inc('tag_assign_total', ['tenant'=>$tenant], 1); }
    public static function incUnassign(string $tenant): void { self::exporter()->inc('tag_unassign_total', ['tenant'=>$tenant], 1); }
    public static function incSearch(string $tenant): void { self::exporter()->inc('tag_search_total', ['tenant'=>$tenant], 1); }
    public static function observeLatency(string $route, float $seconds): void { self::exporter()->observe('http_request_duration_seconds', $seconds, ['route'=>$route]); }
}
