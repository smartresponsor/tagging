<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\RepositoryInterface;

interface TagPolicyRepositoryInterface
{
    public function getPolicy(string $vendorId): array;

    public function setPolicy(string $vendorId, array $policy): void;

    public function setTagFlags(string $vendorId, string $tagId, bool $required, bool $modOnly): void;
}
