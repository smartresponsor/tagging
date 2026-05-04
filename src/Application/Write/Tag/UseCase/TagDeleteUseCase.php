<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Application\Write\Tag\UseCase;

use App\Tagging\Application\Write\Tag\Dto\TagDeleteCommand;
use App\Tagging\Application\Write\Tag\Dto\TagError;
use App\Tagging\Application\Write\Tag\Dto\TagResult;
use App\Tagging\Cache\Store\Tag\TagSearchCache;
use App\Tagging\Cache\Store\Tag\TagSuggestCache;
use App\Tagging\Cache\Store\Tag\TagQueryCacheInvalidator;
use App\Tagging\Service\Core\TagEntityRepositoryInterface;
use App\Tagging\Service\Core\TagTransactionRunnerInterface;

final readonly class TagDeleteUseCase implements TagDeleteUseCaseInterface
{
    private TagQueryCacheInvalidator $cacheInvalidator;

    public function __construct(
        private TagEntityRepositoryInterface $repo,
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

    public function execute(TagDeleteCommand $command): TagResult
    {
        if ('' === $command->tenant) {
            return TagResult::failure(TagError::InvalidTenant);
        }

        if (null === $this->repo->findById($command->tenant, $command->id)) {
            return TagResult::failure(TagError::NotFound);
        }

        $this->transaction->run(function () use ($command): void {
            $this->repo->delete($command->tenant, $command->id);
        });

        $this->cacheInvalidator->clearTenant($command->tenant);

        return TagResult::success(204);
    }
}
