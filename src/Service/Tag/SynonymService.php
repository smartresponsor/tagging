<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Tag;

use App\Data\Tag\FileTagSynonymRepository;

/**
 *
 */

/**
 *
 */
final class SynonymService
{
    /**
     * @param \App\Data\Tag\FileTagSynonymRepository $repo
     */
    public function __construct(private readonly FileTagSynonymRepository $repo = new FileTagSynonymRepository())
    {
    }

    public function list(string $tagId): array
    {
        return ['items' => $this->repo->list($tagId)];
    }

    /**
     * @param string $tagId
     * @param string $label
     * @return int[]
     */
    public function add(string $tagId, string $label): array
    {
        $ok = $this->repo->add($tagId, $label);
        return ['added' => $ok ? 1 : 0];
    }

    /**
     * @param string $tagId
     * @param string $label
     * @return int[]
     */
    public function remove(string $tagId, string $label): array
    {
        $ok = $this->repo->remove($tagId, $label);
        return ['removed' => $ok ? 1 : 0];
    }
}
