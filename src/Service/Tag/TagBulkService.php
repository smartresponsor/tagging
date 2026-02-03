<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Service\Tag;

use App\ServiceInterface\Tag\TagBulkRepositoryInterface;

final class TagBulkService {
    public function __construct(private TagBulkRepositoryInterface $repo){}

    public function bulkImport(array $items): string {
        $jobId = bin2hex(random_bytes(16));
        $this->repo->createJob($jobId, 'import');
        foreach ($items as $p) {
            $this->repo->addItem(bin2hex(random_bytes(16)), $jobId, $p);
        }
        // In a real system, enqueue background worker
        return $jobId;
    }

    public function jobStatus(string $jobId): array {
        return $this->repo->getJob($jobId);
    }

    public function merge(string $from, string $to, bool $moveAssignments=true, bool $copySynonyms=true): array {
        return $this->repo->mergeTags($from, $to, $moveAssignments, $copySynonyms);
    }

    public function split(string $id, array $newTags): array {
        return $this->repo->splitTag($id, $newTags);
    }

    public function resolveRedirect(string $fromTagId): ?string {
        return $this->repo->resolveRedirect($fromTagId);
    }
}
