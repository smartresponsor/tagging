<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag\Middleware;

use App\Service\Core\Tag\Metric\TagMetrics;

final readonly class Observe
{
    private const READ_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function __construct(private array $cfg) {}

    public function handle(array $request, callable $next): array
    {
        $startedAt = microtime(true);
        $response = $next($request);

        $method = strtoupper((string) ($request['method'] ?? 'GET'));
        $path = (string) ($request['path'] ?? '/');
        $op = $this->operationForMethod($method);
        $status = (int) ($response[0] ?? 200);
        $latencyMs = (int) round(1000.0 * (microtime(true) - $startedAt));

        $this->recordMetrics($op, $status, $latencyMs);
        $this->writeSlowLog($method, $path, $op, $status, $latencyMs);

        return $response;
    }

    private function operationForMethod(string $method): string
    {
        return in_array($method, self::READ_METHODS, true) ? 'read' : 'write';
    }

    private function recordMetrics(string $op, int $status, int $latencyMs): void
    {
        TagMetrics::observe('tag_request_latency_seconds', $latencyMs / 1000.0, ['op' => $op]);

        if ($status >= 500) {
            TagMetrics::inc('tag_error_total', 1.0, ['op' => $op, 'cls' => '5xx']);
        } elseif ($status >= 400) {
            TagMetrics::inc('tag_error_total', 1.0, ['op' => $op, 'cls' => '4xx']);
        }
    }

    private function writeSlowLog(string $method, string $path, string $op, int $status, int $latencyMs): void
    {
        if ($latencyMs <= $this->thresholdFor($op)) {
            return;
        }

        $line = json_encode([
            'ts' => gmdate('c'),
            'op' => $op,
            'ms' => $latencyMs,
            'code' => $status,
            'path' => $path,
            'method' => $method,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (false === $line) {
            return;
        }

        $pathFile = (string) ($this->cfg['slowlog_path'] ?? 'report/tag/slowlog.ndjson');
        $dir = dirname($pathFile);
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            return;
        }

        file_put_contents($pathFile, $line . "\n", FILE_APPEND | LOCK_EX);
    }

    private function thresholdFor(string $op): int
    {
        return (int) ($this->cfg['threshold_ms'][$op] ?? ('read' === $op ? 500 : 1000));
    }
}
