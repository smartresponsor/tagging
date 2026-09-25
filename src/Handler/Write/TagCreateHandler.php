<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Handler\Write;

use App\Tagging\Command\Input\TagCreateCommand;
use App\Tagging\Enum\TagError;
use App\Tagging\DTO\Write\TagResultDTO;
use App\Tagging\HandlerInterface\Write\TagCreateHandlerInterface;
use App\Tagging\Cache\Store\Tag\TagSearchCache;
use App\Tagging\Cache\Store\Tag\TagSuggestCache;
use App\Tagging\Cache\Store\Tag\TagQueryCacheInvalidator;
use App\Tagging\Service\Core\Record\TagEntityCreateRecord;
use App\Tagging\Policy\Slug\TagSlugPolicy;
use App\Tagging\Service\Core\TagCrudRepositoryInterface;
use App\Tagging\RepositoryInterface\TagTransactionRunnerInterface;
use Random\RandomException;

final readonly class TagCreateHandler implements TagCreateHandlerInterface
{
    private TagQueryCacheInvalidator $cacheInvalidator;

    public function __construct(
        private TagCrudRepositoryInterface $repo,
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

    public function execute(TagCreateCommand $command): TagResultDTO
    {
        if ('' === $command->tenant) {
            return TagResultDTO::failure(TagError::InvalidTenant);
        }

        $nameEntity = trim((string) ($command->payload['nameEntity'] ?? ''));
        if ('' === $nameEntity) {
            return TagResultDTO::failure(TagError::ValidationFailed);
        }

        $slug = trim((string) ($command->payload['slug'] ?? ''));
        if ('' === $slug) {
            $slug = $this->slugPolicy->make($command->tenant, $nameEntity);
        }
        if (!$this->slugPolicy->validate($slug)) {
            return TagResultDTO::failure(TagError::ValidationFailed);
        }

        $locale = trim((string) ($command->payload['locale'] ?? 'en'));
        if ('' === $locale) {
            return TagResultDTO::failure(TagError::ValidationFailed);
        }

        $weightRaw = $command->payload['weight'] ?? 0;
        if (!is_int($weightRaw) && !(is_string($weightRaw) && is_numeric($weightRaw))) {
            return TagResultDTO::failure(TagError::ValidationFailed);
        }
        $weight = (int) $weightRaw;

        try {
            /** @var array<string,mixed> $created */
            $created = $this->transaction->run(function () use ($command, $slug, $nameEntity, $locale, $weight): array {
                return $this->repo->create(
                    $command->tenant,
                    new TagEntityCreateRecord($this->ulid(), $slug, $nameEntity, $locale, $weight),
                );
            });

            $this->cacheInvalidator->clearTenant($command->tenant);

            return TagResultDTO::success(201, $created);
        } catch (\RuntimeException $e) {
            if ('slug_conflict' === $e->getMessage()) {
                return TagResultDTO::failure(TagError::Conflict);
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
