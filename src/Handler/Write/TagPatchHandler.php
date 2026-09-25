<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Handler\Write;

use App\Tagging\Command\Input\TagPatchCommand;
use App\Tagging\Enum\TagError;
use App\Tagging\DTO\Write\TagResultDTO;
use App\Tagging\HandlerInterface\Write\TagPatchHandlerInterface;
use App\Tagging\Cache\Store\Tag\TagSearchCache;
use App\Tagging\Cache\Store\Tag\TagSuggestCache;
use App\Tagging\Cache\Store\Tag\TagQueryCacheInvalidator;
use App\Tagging\Service\Core\TagCrudRepositoryInterface;
use App\Tagging\RepositoryInterface\TagTransactionRunnerInterface;

final readonly class TagPatchHandler implements TagPatchHandlerInterface
{
    private TagQueryCacheInvalidator $cacheInvalidator;

    public function __construct(
        private TagCrudRepositoryInterface $repo,
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

    public function execute(TagPatchCommand $command): TagResultDTO
    {
        if ('' === $command->tenant) {
            return TagResultDTO::failure(TagError::InvalidTenant);
        }

        $patch = $this->normalizePatch($command->payload);
        if (null === $patch) {
            return TagResultDTO::failure(TagError::ValidationFailed);
        }

        if (null === $this->repo->findById($command->tenant, $command->id)) {
            return TagResultDTO::failure(TagError::NotFound);
        }

        $this->transaction->run(function () use ($command, $patch): void {
            $this->repo->patch($command->tenant, $command->id, $patch);
        });

        $this->cacheInvalidator->clearTenant($command->tenant);

        return TagResultDTO::success(200, ['id' => $command->id]);
    }

    /** @param array<string,mixed> $payload
     * @return array{nameEntity?:string,locale?:string,weight?:int}|null
     */
    private function normalizePatch(array $payload): ?array
    {
        $patch = [];

        if (array_key_exists('nameEntity', $payload)) {
            $nameEntity = trim((string) $payload['nameEntity']);
            if ('' === $nameEntity) {
                return null;
            }
            $patch['nameEntity'] = $nameEntity;
        }

        if (array_key_exists('locale', $payload)) {
            $locale = trim((string) $payload['locale']);
            if ('' === $locale) {
                return null;
            }
            $patch['locale'] = $locale;
        }

        if (array_key_exists('weight', $payload)) {
            $weightRaw = $payload['weight'];
            if (!is_int($weightRaw) && !(is_string($weightRaw) && is_numeric($weightRaw))) {
                return null;
            }
            $patch['weight'] = (int) $weightRaw;
        }

        return [] === $patch ? null : $patch;
    }
}
