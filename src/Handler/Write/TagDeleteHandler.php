<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Handler\Write;

use App\Tagging\Command\Input\TagDeleteCommand;
use App\Tagging\Enum\TagError;
use App\Tagging\DTO\Write\TagResultDTO;
use App\Tagging\HandlerInterface\Write\TagDeleteHandlerInterface;
use App\Tagging\Cache\Store\Tag\TagSearchCache;
use App\Tagging\Cache\Store\Tag\TagSuggestCache;
use App\Tagging\Cache\Store\Tag\TagQueryCacheInvalidator;
use App\Tagging\Service\Core\TagCrudRepositoryInterface;
use App\Tagging\RepositoryInterface\TagTransactionRunnerInterface;

final readonly class TagDeleteHandler implements TagDeleteHandlerInterface
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

    public function execute(TagDeleteCommand $command): TagResultDTO
    {
        if ('' === $command->tenant) {
            return TagResultDTO::failure(TagError::InvalidTenant);
        }

        if (null === $this->repo->findById($command->tenant, $command->id)) {
            return TagResultDTO::failure(TagError::NotFound);
        }

        $this->transaction->run(function () use ($command): void {
            $this->repo->delete($command->tenant, $command->id);
        });

        $this->cacheInvalidator->clearTenant($command->tenant);

        return TagResultDTO::success(204);
    }
}
