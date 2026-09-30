<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core;

use Random\RandomException;

final readonly class TagBulkService
{
    public function __construct(private TagBulkRepositoryInterface $repo) {}

    /**
     * @throws RandomException
     */
    public function bulkImport(string $vendorId, array $items): string
    {
        $jobId = bin2hex(random_bytes(16));
        $this->repo->createJob($vendorId, $jobId, 'import');
        foreach ($items as $p) {
            $this->repo->addItem($vendorId, bin2hex(random_bytes(16)), $jobId, $p);
        }

        return $jobId;
    }

    public function jobStatus(string $vendorId, string $jobId): array
    {
        return $this->repo->getJob($vendorId, $jobId);
    }

    public function merge(
        string $vendorId,
        string $from,
        string $to,
        bool $moveAssignments = true,
        bool $copySynonyms = true,
    ): array {
        return $this->repo->mergeTags($vendorId, $from, $to, $moveAssignments, $copySynonyms);
    }

    public function split(string $vendorId, string $id, array $newTags): array
    {
        return $this->repo->splitTag($vendorId, $id, $newTags);
    }

    public function resolveRedirect(string $vendorId, string $fromTagId): ?string
    {
        return $this->repo->resolveRedirect($vendorId, $fromTagId);
    }
}
