<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\Service\Tag;

use App\Data\Tag\FileTagSynonymRepository;

final class SynonymService
{
    public function __construct(private FileTagSynonymRepository $repo = new FileTagSynonymRepository()){}

    public function list(string $tagId): array
    {
        return ['items' => $this->repo->list($tagId)];
    }

    public function add(string $tagId, string $label): array
    {
        $ok = $this->repo->add($tagId, $label);
        return ['added' => $ok ? 1 : 0];
    }

    public function remove(string $tagId, string $label): array
    {
        $ok = $this->repo->remove($tagId, $label);
        return ['removed' => $ok ? 1 : 0];
    }
}
