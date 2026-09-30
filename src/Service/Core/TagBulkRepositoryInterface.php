<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core;

interface TagBulkRepositoryInterface
{
    public function createJob(string $vendorId, string $id, string $type): void;

    public function setJobStatus(string $vendorId, string $id, string $status, ?string $error = null): void;

    public function addItem(string $vendorId, string $id, string $jobId, array $payload): void;

    public function listItems(string $vendorId, string $jobId): array;

    public function getJob(string $vendorId, string $jobId): array;

    public function resolveRedirect(string $vendorId, string $fromTagId): ?string;

    public function mergeTags(
        string $vendorId,
        string $from,
        string $to,
        bool $moveAssignments = true,
        bool $copySynonyms = true,
    ): array;

    public function splitTag(string $vendorId, string $id, array $newTags): array;
}
