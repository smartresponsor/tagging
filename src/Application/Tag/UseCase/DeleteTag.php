<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Application\Tag\UseCase;

use App\Application\Tag\Dto\DeleteTagCommand;
use App\Application\Tag\Dto\TagError;
use App\Application\Tag\Dto\TagResult;
use App\ServiceInterface\Tag\TagEntityRepositoryInterface;
use App\ServiceInterface\Tag\TransactionRunnerInterface;

final class DeleteTag
{
    public function __construct(
        private TagEntityRepositoryInterface $repo,
        private TransactionRunnerInterface $transaction,
    ) {
    }

    public function execute(DeleteTagCommand $command): TagResult
    {
        if ($command->tenant === '') {
            return TagResult::failure(TagError::InvalidTenant);
        }

        if ($this->repo->findById($command->tenant, $command->id) === null) {
            return TagResult::failure(TagError::NotFound);
        }

        $this->transaction->run(function () use ($command): void {
            $this->repo->delete($command->tenant, $command->id);
        });

        return TagResult::success(204);
    }
}
