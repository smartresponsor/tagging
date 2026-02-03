<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\Http\Tag\Middleware;

use App\Service\Tag\Metric\TagMetrics;

final class Observe
{
    public function __construct(private array $cfg){}

    public function handle(array $request, callable $next): array
    {
        $t0 = microtime(true);
        $resp = $next($request);
        $t1 = microtime(true);

        $code = (int)($resp[0] ?? 200);
        $method = strtoupper((string)($request['method'] ?? 'GET'));
        $path   = (string)($request['path'] ?? '/');
        $op = in_array($method, ['GET','HEAD','OPTIONS'], true) ? 'read' : 'write';
        $ms = (int)round(1000.0 * ($t1 - $t0));

        // latency summary
        TagMetrics::observe('tag_request_latency_seconds', $ms / 1000.0, ['op'=>$op]);

        // error class counters
        if ($code >= 500) {
            TagMetrics::inc('tag_error_total', 1.0, ['op'=>$op, 'cls'=>'5xx']);
        } elseif ($code >= 400) {
            TagMetrics::inc('tag_error_total', 1.0, ['op'=>$op, 'cls'=>'4xx']);
        }

        // slowlog
        $thr = (int)($this->cfg['threshold_ms'][$op] ?? ($op==='read' ? 500 : 1000));
        if ($ms > $thr) {
            $line = json_encode([
                'ts' => gmdate('c'),
                'op' => $op,
                'ms' => $ms,
                'code' => $code,
                'path' => $path,
                'method' => $method,
            ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            $pathFile = (string)($this->cfg['slowlog_path'] ?? 'report/tag/slowlog.ndjson');
            $dir = dirname($pathFile);
            if (!is_dir($dir)) @mkdir($dir, 0777, true);
            file_put_contents($pathFile, $line . "\n", FILE_APPEND);
        }

        return $resp;
    }
}
