<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Tag;

use App\Data\Tag\FileTagRedirectRepository;

/**
 *
 */

/**
 *
 */
final class RedirectResolver
{
    /**
     * @param \App\Data\Tag\FileTagRedirectRepository $repo
     */
    public function __construct(private readonly FileTagRedirectRepository $repo = new FileTagRedirectRepository())
    {
    }

    /**
     * @param string $fromId
     * @return array
     */
    public function getTarget(string $fromId): array
    {
        $to = $this->repo->resolve($fromId);
        return ['fromId' => $fromId, 'toId' => $to];
    }

    /**
     * @param string $fromId
     * @param string $toId
     * @return void
     */
    public function record(string $fromId, string $toId): void
    {
        if ($fromId === '' || $toId === '' || $fromId === $toId) return;
        $this->repo->put($fromId, $toId);
    }
}
