<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Data\Tag;

final class MultiTenantLabelRepository extends FileTagLabelRepository
{
    /**
     * @param string $tenantId
     * @param string $baseDir
     */
    public function __construct(string $tenantId, string $baseDir='report/tag')
    {
        parent::__construct(rtrim($baseDir,'/').'/'.rawurlencode($tenantId).'/label.ndjson');
    }
}
