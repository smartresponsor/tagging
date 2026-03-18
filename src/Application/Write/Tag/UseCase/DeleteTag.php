<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Application\Write\Tag\UseCase;

use App\Application\Write\Tag\Dto\DeleteTagCommand;
use App\Application\Write\Tag\Dto\TagError;
use App\Application\Write\Tag\Dto\TagResult;
use App\Cache\Store\Tag\SearchCache;
use App\Cache\Store\Tag\SuggestCache;
use App\ServiceInterface\Core\Tag\TagEntityRepositoryInterface;
use App\ServiceInterface\Core\Tag\TransactionRunnerInterface;

final readonly class DeleteTag
{
    public function __construct(
        private TagEntityRepositoryInterface $repo,
        private TransactionRunnerInterface $transaction,
        private ?SearchCache $searchCache = null,
        private ?SuggestCache $suggestCache = null,
    ) {
    }

    public function execute(DeleteTagCommand $command): TagResult
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

        $this->searchCache?->clearTenant($command->tenant);
        $this->suggestCache?->clearTenant($command->tenant);

        return TagResult::success(204);
    }
}
