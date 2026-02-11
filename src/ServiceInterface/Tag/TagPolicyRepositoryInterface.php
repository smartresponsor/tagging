<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Tag;

/**
 *
 */

/**
 *
 */
interface TagPolicyRepositoryInterface
{
    /**
     * @param string $tenantId
     * @return array
     */
    public function getPolicy(string $tenantId): array;

    /**
     * @param string $tenantId
     * @param array $policy
     * @return void
     */
    public function setPolicy(string $tenantId, array $policy): void;

    /**
     * @param string $tenantId
     * @param string $tagId
     * @param bool $required
     * @param bool $modOnly
     * @return void
     */
    public function setTagFlags(string $tenantId, string $tagId, bool $required, bool $modOnly): void;
}
