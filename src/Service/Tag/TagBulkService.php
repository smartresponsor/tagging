<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Tag;

use App\ServiceInterface\Tag\TagBulkRepositoryInterface;

/**
 *
 */

/**
 *
 */
final readonly class TagBulkService
{
    /**
     * @param \App\ServiceInterface\Tag\TagBulkRepositoryInterface $repo
     */
    public function __construct(private TagBulkRepositoryInterface $repo)
    {
    }

    /**
     * @param string $tenantId
     * @param array $items
     * @return string
     * @throws \Random\RandomException
     */
    public function bulkImport(string $tenantId, array $items): string
    {
        $jobId = bin2hex(random_bytes(16));
        $this->repo->createJob($tenantId, $jobId, 'import');
        foreach ($items as $p) {
            $this->repo->addItem($tenantId, bin2hex(random_bytes(16)), $jobId, $p);
        }
        return $jobId;
    }

    /**
     * @param string $tenantId
     * @param string $jobId
     * @return array
     */
    public function jobStatus(string $tenantId, string $jobId): array
    {
        return $this->repo->getJob($tenantId, $jobId);
    }

    /**
     * @param string $tenantId
     * @param string $from
     * @param string $to
     * @param bool $moveAssignments
     * @param bool $copySynonyms
     * @return array
     */
    public function merge(string $tenantId, string $from, string $to, bool $moveAssignments = true, bool $copySynonyms = true): array
    {
        return $this->repo->mergeTags($tenantId, $from, $to, $moveAssignments, $copySynonyms);
    }

    /**
     * @param string $tenantId
     * @param string $id
     * @param array $newTags
     * @return array
     */
    public function split(string $tenantId, string $id, array $newTags): array
    {
        return $this->repo->splitTag($tenantId, $id, $newTags);
    }

    /**
     * @param string $tenantId
     * @param string $fromTagId
     * @return string|null
     */
    public function resolveRedirect(string $tenantId, string $fromTagId): ?string
    {
        return $this->repo->resolveRedirect($tenantId, $fromTagId);
    }
}
