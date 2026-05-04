<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Application\Write\Tag\UseCase;

use App\Tagging\Application\Write\Tag\Dto\TagCreateCommand;
use App\Tagging\Application\Write\Tag\Dto\TagError;
use App\Tagging\Application\Write\Tag\Dto\TagResult;
use App\Tagging\Cache\Store\Tag\TagSearchCache;
use App\Tagging\Cache\Store\Tag\TagSuggestCache;
use App\Tagging\Cache\Store\Tag\TagQueryCacheInvalidator;
use App\Tagging\Service\Core\Record\TagEntityCreateRecord;
use App\Tagging\Service\Core\Slug\TagSlugPolicy;
use App\Tagging\Service\Core\TagEntityRepositoryInterface;
use App\Tagging\Service\Core\TagTransactionRunnerInterface;
use Random\RandomException;

final readonly class TagCreateUseCase implements TagCreateUseCaseInterface
{
    private TagQueryCacheInvalidator $cacheInvalidator;

    public function __construct(
        private TagEntityRepositoryInterface $repo,
        private TagSlugPolicy $slugPolicy,
        private TagTransactionRunnerInterface $transaction,
        private ?TagSearchCache $searchCache = null,
        private ?TagSuggestCache $suggestCache = null,
        ?TagQueryCacheInvalidator $cacheInvalidator = null,
    ) {
        $this->cacheInvalidator = $cacheInvalidator
            ?? new TagQueryCacheInvalidator(
                $this->searchCache,
                $this->suggestCache,
            );
    }

    public function execute(TagCreateCommand $command): TagResult
    {
        if ('' === $command->tenant) {
            return TagResult::failure(TagError::InvalidTenant);
        }

        $name = trim((string) ($command->payload['name'] ?? ''));
        if ('' === $name) {
            return TagResult::failure(TagError::ValidationFailed);
        }

        $slug = trim((string) ($command->payload['slug'] ?? ''));
        if ('' === $slug) {
            $slug = $this->slugPolicy->make($command->tenant, $name);
        }
        if (!$this->slugPolicy->validate($slug)) {
            return TagResult::failure(TagError::ValidationFailed);
        }

        $locale = trim((string) ($command->payload['locale'] ?? 'en'));
        if ('' === $locale) {
            return TagResult::failure(TagError::ValidationFailed);
        }

        $weightRaw = $command->payload['weight'] ?? 0;
        if (!is_int($weightRaw) && !(is_string($weightRaw) && is_numeric($weightRaw))) {
            return TagResult::failure(TagError::ValidationFailed);
        }
        $weight = (int) $weightRaw;

        try {
            /** @var array<string,mixed> $created */
            $created = $this->transaction->run(function () use ($command, $slug, $name, $locale, $weight): array {
                return $this->repo->create(
                    $command->tenant,
                    new TagEntityCreateRecord($this->ulid(), $slug, $name, $locale, $weight),
                );
            });

            $this->cacheInvalidator->clearTenant($command->tenant);

            return TagResult::success(201, $created);
        } catch (\RuntimeException $e) {
            if ('slug_conflict' === $e->getMessage()) {
                return TagResult::failure(TagError::Conflict);
            }
            throw $e;
        }
    }

    /**
     * @throws RandomException
     */
    private function ulid(): string
    {
        return substr(strtoupper(bin2hex(random_bytes(13))), 0, 26);
    }
}
