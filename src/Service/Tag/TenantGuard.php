<?php
declare(strict_types=1);
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
namespace App\Service\Tag;

final class TenantGuard
{
    public function __construct(private array $cfg = []) {}

    /** @param array<string,string> $headers */
    public function requireTenant(array $headers): string
    {
        $header = (string)($this->cfg['header'] ?? 'X-Tenant-Id');
        $tenant = (string)($headers[$header] ?? '');
        if ($tenant === '') {
            $def = (string)($this->cfg['default_tenant'] ?? '');
            if ($def !== '') return $def;
            $code = (int)($this->cfg['responses']['on_missing'] ?? 401);
            http_response_code($code);
            header('Content-Type: application/json');
            echo json_encode(['code'=>'tenant_missing','header'=>$header]);
            exit;
        }
        return $tenant;
    }
}
