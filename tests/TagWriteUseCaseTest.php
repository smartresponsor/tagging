<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Tagging\Application\Write\Tag\Dto\TagCreateCommand;
use App\Tagging\Application\Write\Tag\Dto\TagDeleteCommand;
use App\Tagging\Application\Write\Tag\Dto\TagPatchCommand;
use App\Tagging\Application\Write\Tag\UseCase\TagCreateUseCase;
use App\Tagging\Application\Write\Tag\UseCase\TagDeleteUseCase;
use App\Tagging\Application\Write\Tag\UseCase\TagPatchUseCase;
use App\Tagging\Http\Api\Tag\Responder\TagWriteResponder;
use App\Tagging\Service\Core\Record\TagEntityCreateRecord;
use App\Tagging\Service\Core\Slug\TagSlugifier;
use App\Tagging\Service\Core\Slug\TagSlugPolicy;
use App\Tagging\Service\Core\TagEntityRepositoryInterface;
use App\Tagging\Service\Core\TagTransactionRunnerInterface;
use PHPUnit\Framework\TestCase;

final class TagWriteUseCaseTest extends TestCase
{
    public function testCreateTagMapsUniqueViolationToConflict(): void
    {
        $repo = new class implements TagEntityRepositoryInterface {
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
        $useCase = new TagCreateUseCase($repo, $policy, $tx);

        $result = $useCase->execute(
            new TagCreateCommand('tenant-a', ['name' => 'Alpha', 'slug' => 'alpha']),
        );
        $response = (new TagWriteResponder())->respond($result);

        self::assertSame(409, $response[0]);
        self::assertStringContainsString('conflict', $response[2]);
    }

    public function testPatchTagReturnsNotFoundWhenEntityMissing(): void
    {
        $repo = new class implements TagEntityRepositoryInterface {
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

        $useCase = new TagPatchUseCase($repo, $tx);
        $result = $useCase->execute(
            new TagPatchCommand('tenant-a', '01ARZ3NDEKTSV4RRFFQ69G5FAV', ['name' => 'Beta']),
        );
        $response = (new TagWriteResponder())->respond($result);

        self::assertSame(404, $response[0]);
        self::assertStringContainsString('not_found', $response[2]);
    }

    public function testDeleteTagReturnsNoContentWhenEntityExists(): void
    {
        $repo = new class implements TagEntityRepositoryInterface {
            public bool $deleted = false;

            public function existsSlug(string $tenant, string $slug, ?string $excludeId = null): bool
            {
                return false;
            }

            public function findById(string $tenant, string $id): ?array
            {
                return ['id' => $id, 'slug' => 'x', 'name' => 'x', 'locale' => 'en', 'weight' => 0];
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

        $useCase = new TagDeleteUseCase($repo, $tx);
        $result = $useCase->execute(
            new TagDeleteCommand('tenant-a', '01ARZ3NDEKTSV4RRFFQ69G5FAV'),
        );
        $response = (new TagWriteResponder())->respond($result);

        self::assertSame(204, $response[0]);
        self::assertSame('', $response[2]);
        self::assertTrue($repo->deleted);
    }
}
