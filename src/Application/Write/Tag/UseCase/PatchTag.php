<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Application\Write\Tag\UseCase;

use App\Application\Write\Tag\Dto\PatchTagCommand;
use App\Application\Write\Tag\Dto\TagError;
use App\Application\Write\Tag\Dto\TagResult;
use App\Cache\Store\Tag\SearchCache;
use App\Cache\Store\Tag\SuggestCache;
use App\Cache\Store\Tag\TagQueryCacheInvalidator;
use App\Service\Core\Tag\TagEntityRepositoryInterface;
use App\Service\Core\Tag\TransactionRunnerInterface;

final class PatchTag implements PatchTagInterface
{
    private TagQueryCacheInvalidator $cacheInvalidator;

    public function __construct(
        private TagEntityRepositoryInterface $repo,
        private TransactionRunnerInterface $transaction,
        private ?SearchCache $searchCache = null,
        private ?SuggestCache $suggestCache = null,
        ?TagQueryCacheInvalidator $cacheInvalidator = null,
    ) {
        $this->cacheInvalidator = $cacheInvalidator ?? new TagQueryCacheInvalidator($this->searchCache, $this->suggestCache);
    }

    public function execute(PatchTagCommand $command): TagResult
    {
        if ('' === $command->tenant) {
            return TagResult::failure(TagError::InvalidTenant);
        }

        $patch = $this->normalizePatch($command->payload);
        if (null === $patch) {
            return TagResult::failure(TagError::ValidationFailed);
        }

        if (null === $this->repo->findById($command->tenant, $command->id)) {
            return TagResult::failure(TagError::NotFound);
        }

        $this->transaction->run(function () use ($command, $patch): void {
            $this->repo->patch($command->tenant, $command->id, $patch);
        });

        $this->cacheInvalidator->clearTenant($command->tenant);

        return TagResult::success(200, ['id' => $command->id]);
    }

    /** @param array<string,mixed> $payload
     * @return array{name?:string,locale?:string,weight?:int}|null
     */
    private function normalizePatch(array $payload): ?array
    {
        $patch = [];

        if (array_key_exists('name', $payload)) {
            $name = trim((string) $payload['name']);
            if ('' === $name) {
                return null;
            }
            $patch['name'] = $name;
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
