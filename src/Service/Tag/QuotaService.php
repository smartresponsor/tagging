<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Tag;

/**
 *
 */

/**
 *
 */
final readonly class QuotaService
{
    /**
     * @param array $cfg
     */
    public function __construct(private array $cfg = [])
    {
    }

    /**
     * @param string $tenantId
     * @return array|true[]
     */
    public function canCreateTag(string $tenantId): array
    {
        $max = (int)($this->cfg['quotas']['max_tags'] ?? 0);
        if ($max <= 0) return ['ok' => true];
        $count = $this->countLines($this->path($tenantId, 'label.ndjson'));
        return ['ok' => $count < $max, 'used' => $count, 'max' => $max];
    }

    /**
     * @param string $tenantId
     * @return array|true[]
     */
    public function canAssign(string $tenantId): array
    {
        $max = (int)($this->cfg['quotas']['max_assignments'] ?? 0);
        if ($max <= 0) return ['ok' => true];
        $count = $this->countLines($this->path($tenantId, 'assignment.ndjson'));
        return ['ok' => $count < $max, 'used' => $count, 'max' => $max];
    }

    /**
     * @return string
     */
    private function base(): string
    {
        return (string)($this->cfg['paths']['base'] ?? 'report/tag');
    }

    /**
     * @param string $tenantId
     * @param string $file
     * @return string
     */
    private function path(string $tenantId, string $file): string
    {
        $p = rtrim($this->base(), '/') . '/' . rawurlencode($tenantId) . '/' . $file;
        $d = dirname($p);
        if (!is_dir($d)) @mkdir($d, 0777, true);
        if (!file_exists($p)) file_put_contents($p, '');
        return $p;
    }

    /**
     * @param string $path
     * @return int
     */
    private function countLines(string $path): int
    {
        $c = 0;
        $h = @fopen($path, 'r');
        if ($h) {
            while (fgets($h) !== false) {
                $c++;
            }
            fclose($h);
        }
        return $c;
    }
}
