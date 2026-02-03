<?php
declare(strict_types=1);

namespace App\ServiceInterface\Tag;

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
interface TagBulkRepositoryInterface
{
    public function createJob(string $id, string $type): void;

    public function setJobStatus(string $id, string $status, ?string $error = null): void;

    public function addItem(string $id, string $jobId, array $payload): void;

    public function listItems(string $jobId): array;

    public function getJob(string $jobId): array;

    public function resolveRedirect(string $fromTagId): ?string;

    public function mergeTags(string $from, string $to, bool $moveAssignments = true, bool $copySynonyms = true): array;

    public function splitTag(string $id, array $newTags): array;
}
