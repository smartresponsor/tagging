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
interface TagBulkRepositoryInterface
{
    /**
     * @param string $tenantId
     * @param string $id
     * @param string $type
     * @return void
     */
    public function createJob(string $tenantId, string $id, string $type): void;

    /**
     * @param string $tenantId
     * @param string $id
     * @param string $status
     * @param string|null $error
     * @return void
     */
    public function setJobStatus(string $tenantId, string $id, string $status, ?string $error = null): void;

    /**
     * @param string $tenantId
     * @param string $id
     * @param string $jobId
     * @param array $payload
     * @return void
     */
    public function addItem(string $tenantId, string $id, string $jobId, array $payload): void;

    /**
     * @param string $tenantId
     * @param string $jobId
     * @return array
     */
    public function listItems(string $tenantId, string $jobId): array;

    /**
     * @param string $tenantId
     * @param string $jobId
     * @return array
     */
    public function getJob(string $tenantId, string $jobId): array;

    /**
     * @param string $tenantId
     * @param string $fromTagId
     * @return string|null
     */
    public function resolveRedirect(string $tenantId, string $fromTagId): ?string;

    /**
     * @param string $tenantId
     * @param string $from
     * @param string $to
     * @param bool $moveAssignments
     * @param bool $copySynonyms
     * @return array
     */
    public function mergeTags(string $tenantId, string $from, string $to, bool $moveAssignments = true, bool $copySynonyms = true): array;

    /**
     * @param string $tenantId
     * @param string $id
     * @param array $newTags
     * @return array
     */
    public function splitTag(string $tenantId, string $id, array $newTags): array;
}
