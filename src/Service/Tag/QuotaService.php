<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\Service\Tag;

final class QuotaService
{
    public function __construct(private array $cfg = []) {}

    public function canCreateTag(string $tenantId): array
    {
        $max = (int)($this->cfg['quotas']['max_tags'] ?? 0);
        if ($max <= 0) return ['ok'=>true];
        $count = $this->countLines($this->path($tenantId, 'label.ndjson'));
        return ['ok' => $count < $max, 'used'=>$count, 'max'=>$max];
    }

    public function canAssign(string $tenantId): array
    {
        $max = (int)($this->cfg['quotas']['max_assignments'] ?? 0);
        if ($max <= 0) return ['ok'=>true];
        $count = $this->countLines($this->path($tenantId, 'assignment.ndjson'));
        return ['ok' => $count < $max, 'used'=>$count, 'max'=>$max];
    }

    private function base(): string
    {
        return (string)($this->cfg['paths']['base'] ?? 'report/tag');
    }

    private function path(string $tenantId, string $file): string
    {
        $p = rtrim($this->base(), '/').'/'.rawurlencode($tenantId).'/'.$file;
        $d = dirname($p);
        if (!is_dir($d)) @mkdir($d, 0777, true);
        if (!file_exists($p)) file_put_contents($p, '');
        return $p;
    }

    private function countLines(string $path): int
    {
        $c=0; $h=@fopen($path,'r');
        if ($h){ while(fgets($h)!==false){ $c++; } fclose($h); }
        return $c;
    }
}
