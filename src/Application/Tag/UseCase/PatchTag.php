<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Application\Tag\UseCase;

use App\Application\Tag\Dto\PatchTagCommand;
use App\Application\Tag\Dto\TagError;
use App\Application\Tag\Dto\TagResult;
use App\ServiceInterface\Tag\TagEntityRepositoryInterface;
use App\ServiceInterface\Tag\TransactionRunnerInterface;

/**
 *
 */

/**
 *
 */
final readonly class PatchTag
{
    /**
     * @param \App\ServiceInterface\Tag\TagEntityRepositoryInterface $repo
     * @param \App\ServiceInterface\Tag\TransactionRunnerInterface $transaction
     */
    public function __construct(
        private TagEntityRepositoryInterface $repo,
        private TransactionRunnerInterface   $transaction,
    )
    {
    }

    /**
     * @param \App\Application\Tag\Dto\PatchTagCommand $command
     * @return \App\Application\Tag\Dto\TagResult
     */
    public function execute(PatchTagCommand $command): TagResult
    {
        if ($command->tenant === '') {
            return TagResult::failure(TagError::InvalidTenant);
        }

        $patch = $this->normalizePatch($command->payload);
        if ($patch === null) {
            return TagResult::failure(TagError::ValidationFailed);
        }

        if ($this->repo->findById($command->tenant, $command->id) === null) {
            return TagResult::failure(TagError::NotFound);
        }

        $this->transaction->run(function () use ($command, $patch): void {
            $this->repo->patch($command->tenant, $command->id, $patch);
        });

        return TagResult::success(200, ['id' => $command->id]);
    }

    /** @param array<string,mixed> $payload
     * @return array{name?:string,locale?:string,weight?:int}|null
     */
    private function normalizePatch(array $payload): ?array
    {
        $patch = [];

        if (array_key_exists('name', $payload)) {
            $name = trim((string)$payload['name']);
            if ($name === '') {
                return null;
            }
            $patch['name'] = $name;
        }

        if (array_key_exists('locale', $payload)) {
            $locale = trim((string)$payload['locale']);
            if ($locale === '') {
                return null;
            }
            $patch['locale'] = $locale;
        }

        if (array_key_exists('weight', $payload)) {
            $weightRaw = $payload['weight'];
            if (!is_int($weightRaw) && !(is_string($weightRaw) && is_numeric($weightRaw))) {
                return null;
            }
            $patch['weight'] = (int)$weightRaw;
        }

        return $patch === [] ? null : $patch;
    }
}
