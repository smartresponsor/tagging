<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Tag;

interface TagPolicyRepositoryInterface
{
    public function getPolicy(string $tenantId): array;

    public function setPolicy(string $tenantId, array $policy): void;

    public function setTagFlags(string $tenantId, string $tagId, bool $required, bool $modOnly): void;
}
