<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Tagging\Command\Input\TagCreateCommand;
use App\Tagging\Command\Input\TagDeleteCommand;
use App\Tagging\Command\Input\TagPatchCommand;
use App\Tagging\Handler\Write\TagCreateHandler;
use App\Tagging\Handler\Write\TagDeleteHandler;
use App\Tagging\Handler\Write\TagPatchHandler;
use App\Tagging\Responder\Api\TagWriteResponder;
use App\Tagging\Service\Core\Record\TagEntityCreateRecord;
use App\Tagging\Service\Core\Slug\TagSlugifier;
use App\Tagging\Policy\Slug\TagSlugPolicy;
use App\Tagging\Service\Core\TagCrudRepositoryInterface;
use App\Tagging\RepositoryInterface\TagTransactionRunnerInterface;
use PHPUnit\Framework\TestCase;

final class TagWriteUseCaseTest extends TestCase
{
    public function testCreateTagMapsUniqueViolationToConflict(): void
    {
        $repo = new class implements TagCrudRepositoryInterface {
            public function existsSlug(string $tenant, string $slug, ?string $excludeId = null): bool
            {
                return false;
            }

            public function findById(string $tenant, string $id): ?array
            {
                return null;
            }

            public function create(string $tenant, TagEntityCreateRecord $record): array
            {
                throw new \RuntimeException('slug_conflict');
            }

            public function patch(string $tenant, string $id, array $patch): void {}

            public function delete(string $tenant, string $id): void {}
        };

        $tx = new class implements TagTransactionRunnerInterface {
            public function run(callable $callback): mixed
            {
                return $callback();
            }
        };

        $policy = new TagSlugPolicy($repo, new TagSlugifier());
        $useCase = new TagCreateHandler($repo, $policy, $tx);

        $result = $useCase->execute(
            new TagCreateCommand('tenant-a', ['nameEntity' => 'Alpha', 'slug' => 'alpha']),
        );
        $response = (new TagWriteResponder())->respond($result);

        self::assertSame(409, $response[0]);
        self::assertStringContainsString('conflict', $response[2]);
    }

    public function testPatchTagReturnsNotFoundWhenEntityMissing(): void
    {
        $repo = new class implements TagCrudRepositoryInterface {
            public function existsSlug(string $tenant, string $slug, ?string $excludeId = null): bool
            {
                return false;
            }

            public function findById(string $tenant, string $id): ?array
            {
                return null;
            }

            public function create(string $tenant, TagEntityCreateRecord $record): array
            {
                return [];
            }

            public function patch(string $tenant, string $id, array $patch): void {}

            public function delete(string $tenant, string $id): void {}
        };

        $tx = new class implements TagTransactionRunnerInterface {
            public function run(callable $callback): mixed
            {
                return $callback();
            }
        };

        $useCase = new TagPatchHandler($repo, $tx);
        $result = $useCase->execute(
            new TagPatchCommand('tenant-a', '01ARZ3NDEKTSV4RRFFQ69G5FAV', ['nameEntity' => 'Beta']),
        );
        $response = (new TagWriteResponder())->respond($result);

        self::assertSame(404, $response[0]);
        self::assertStringContainsString('not_found', $response[2]);
    }

    public function testDeleteTagReturnsNoContentWhenEntityExists(): void
    {
        $repo = new class implements TagCrudRepositoryInterface {
            public bool $deleted = false;

            public function existsSlug(string $tenant, string $slug, ?string $excludeId = null): bool
            {
                return false;
            }

            public function findById(string $tenant, string $id): ?array
            {
                return ['id' => $id, 'slug' => 'x', 'nameEntity' => 'x', 'locale' => 'en', 'weight' => 0];
            }

            public function create(string $tenant, TagEntityCreateRecord $record): array
            {
                return [];
            }

            public function patch(string $tenant, string $id, array $patch): void {}

            public function delete(string $tenant, string $id): void
            {
                $this->deleted = true;
            }
        };

        $tx = new class implements TagTransactionRunnerInterface {
            public function run(callable $callback): mixed
            {
                return $callback();
            }
        };

        $useCase = new TagDeleteHandler($repo, $tx);
        $result = $useCase->execute(
            new TagDeleteCommand('tenant-a', '01ARZ3NDEKTSV4RRFFQ69G5FAV'),
        );
        $response = (new TagWriteResponder())->respond($result);

        self::assertSame(204, $response[0]);
        self::assertSame('', $response[2]);
        self::assertTrue($repo->deleted);
    }
}
