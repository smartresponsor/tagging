<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Application\Tag\UseCase;

use App\Application\Tag\Dto\CreateTagCommand;
use App\Application\Tag\Dto\TagError;
use App\Application\Tag\Dto\TagResult;
use App\Service\Tag\Slug\SlugPolicy;
use App\ServiceInterface\Tag\TagEntityRepositoryInterface;
use App\ServiceInterface\Tag\TransactionRunnerInterface;
use PDOException;

/**
 *
 */

/**
 *
 */
final readonly class CreateTag
{
    /**
     * @param \App\ServiceInterface\Tag\TagEntityRepositoryInterface $repo
     * @param \App\Service\Tag\Slug\SlugPolicy $slugPolicy
     * @param \App\ServiceInterface\Tag\TransactionRunnerInterface $transaction
     */
    public function __construct(
        private TagEntityRepositoryInterface $repo,
        private SlugPolicy                   $slugPolicy,
        private TransactionRunnerInterface   $transaction,
    )
    {
    }

    /**
     * @param \App\Application\Tag\Dto\CreateTagCommand $command
     * @return \App\Application\Tag\Dto\TagResult
     */
    public function execute(CreateTagCommand $command): TagResult
    {
        if ($command->tenant === '') {
            return TagResult::failure(TagError::InvalidTenant);
        }

        $name = trim((string)($command->payload['name'] ?? ''));
        if ($name === '') {
            return TagResult::failure(TagError::ValidationFailed);
        }

        $slug = trim((string)($command->payload['slug'] ?? ''));
        if ($slug === '') {
            $slug = $this->slugPolicy->make($command->tenant, $name);
        }
        if (!$this->slugPolicy->validate($slug)) {
            return TagResult::failure(TagError::ValidationFailed);
        }

        $locale = trim((string)($command->payload['locale'] ?? 'en'));
        if ($locale === '') {
            return TagResult::failure(TagError::ValidationFailed);
        }

        $weightRaw = $command->payload['weight'] ?? 0;
        if (!is_int($weightRaw) && !(is_string($weightRaw) && is_numeric($weightRaw))) {
            return TagResult::failure(TagError::ValidationFailed);
        }
        $weight = (int)$weightRaw;

        try {
            /** @var array<string,mixed> $created */
            $created = $this->transaction->run(function () use ($command, $slug, $name, $locale, $weight): array {
                return $this->repo->create($command->tenant, $this->ulid(), $slug, $name, $locale, $weight);
            });

            return TagResult::success(201, $created);
        } catch (PDOException $e) {
            if (in_array((string)$e->getCode(), ['23505', '23000'], true)) {
                return TagResult::failure(TagError::Conflict);
            }
            throw $e;
        }
    }

    /**
     * @return string
     * @throws \Random\RandomException
     */
    private function ulid(): string
    {
        return substr(strtoupper(bin2hex(random_bytes(13))), 0, 26);
    }
}
