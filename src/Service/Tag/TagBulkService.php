<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Service\Tag;

use App\ServiceInterface\Tag\TagBulkRepositoryInterface;

final class TagBulkService {
    public function __construct(private TagBulkRepositoryInterface $repo){}

    public function bulkImport(string $tenantId, array $items): string {
        $jobId = bin2hex(random_bytes(16));
        $this->repo->createJob($tenantId, $jobId, 'import');
        foreach ($items as $p) {
            $this->repo->addItem($tenantId, bin2hex(random_bytes(16)), $jobId, $p);
        }
        return $jobId;
    }

    public function jobStatus(string $tenantId, string $jobId): array {
        return $this->repo->getJob($tenantId, $jobId);
    }

    public function merge(string $tenantId, string $from, string $to, bool $moveAssignments=true, bool $copySynonyms=true): array {
        return $this->repo->mergeTags($tenantId, $from, $to, $moveAssignments, $copySynonyms);
    }

    public function split(string $tenantId, string $id, array $newTags): array {
        return $this->repo->splitTag($tenantId, $id, $newTags);
    }

    public function resolveRedirect(string $tenantId, string $fromTagId): ?string {
        return $this->repo->resolveRedirect($tenantId, $fromTagId);
    }
}
