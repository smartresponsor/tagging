<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Tag;

interface TagBulkRepositoryInterface
{
    public function createJob(string $tenantId, string $id, string $type): void;

    public function setJobStatus(string $tenantId, string $id, string $status, ?string $error = null): void;

    public function addItem(string $tenantId, string $id, string $jobId, array $payload): void;

    public function listItems(string $tenantId, string $jobId): array;

    public function getJob(string $tenantId, string $jobId): array;

    public function resolveRedirect(string $tenantId, string $fromTagId): ?string;

    public function mergeTags(string $tenantId, string $from, string $to, bool $moveAssignments = true, bool $copySynonyms = true): array;

    public function splitTag(string $tenantId, string $id, array $newTags): array;
}
