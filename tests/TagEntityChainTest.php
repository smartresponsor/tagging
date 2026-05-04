<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Tagging\Application\Write\Tag\UseCase\TagCreateUseCase;
use App\Tagging\Application\Write\Tag\UseCase\TagDeleteUseCase;
use App\Tagging\Application\Write\Tag\UseCase\TagPatchUseCase;
use App\Tagging\Http\Api\Tag\Responder\TagWriteResponder;
use App\Tagging\Http\Api\Tag\TagController;
use App\Tagging\Service\Core\Record\TagEntityCreateRecord;
use App\Tagging\Service\Core\Slug\TagSlugifier;
use App\Tagging\Service\Core\Slug\TagSlugPolicy;
use App\Tagging\Service\Core\TagEntityPayloadNormalizer;
use App\Tagging\Service\Core\TagEntityRepositoryInterface;
use App\Tagging\Service\Core\TagEntityService;
use App\Tagging\Service\Core\TagTransactionRunnerInterface;
use PHPUnit\Framework\TestCase;

final class TagEntityChainTest extends TestCase
{
    public function testEntityRepositoryServiceAndControllerChain(): void
    {
        $repo = new class implements TagEntityRepositoryInterface {
            /** @var array<string,array<string,array<string,mixed>>> */
            private array $rows = [];

            public function existsSlug(string $tenant, string $slug, ?string $excludeId = null): bool
            {
                foreach ($this->rows[$tenant] ?? [] as $row) {
                    if (($row['slug'] ?? null) === $slug && ($excludeId === null || $excludeId !== ($row['id'] ?? null))) {
                        return true;
                    }
                }

                return false;
            }

            public function findById(string $tenant, string $id): ?array
            {
                return $this->rows[$tenant][$id] ?? null;
            }

            public function create(string $tenant, TagEntityCreateRecord $record): array
            {
                return $this->rows[$tenant][$record->id] = $record->toArray();
            }

            public function patch(string $tenant, string $id, array $patch): void
            {
                if (!isset($this->rows[$tenant][$id])) {
                    return;
                }

                $this->rows[$tenant][$id] = array_replace($this->rows[$tenant][$id], $patch);
            }

            public function delete(string $tenant, string $id): void
            {
                unset($this->rows[$tenant][$id]);
            }
        };

        $slugPolicy = new TagSlugPolicy($repo, new TagSlugifier());
        $normalizer = new TagEntityPayloadNormalizer();
        $service = new TagEntityService($repo, $slugPolicy, $normalizer);
        $tx = new class implements TagTransactionRunnerInterface {
            public function run(callable $callback): mixed
            {
                return $callback();
            }
        };
        $controller = new TagController(
            $service,
            new TagCreateUseCase($repo, $slugPolicy, $tx),
            new TagPatchUseCase($repo, $tx),
            new TagDeleteUseCase($repo, $tx),
            new TagWriteResponder(),
        );

        [$createCode, , $createBody] = $controller->create([
            'headers' => ['x-tenant-id' => 'tenant-a'],
            'body' => ['name' => '  Priority Alpha  ', 'locale' => ' uk ', 'weight' => '7'],
        ]);
        self::assertSame(201, $createCode);

        $created = json_decode($createBody, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('priority-alpha', $created['slug']);
        self::assertSame('Priority Alpha', $created['name']);
        self::assertSame('uk', $created['locale']);
        self::assertSame(7, $created['weight']);

        [$getCode, , $getBody] = $controller->get([
            'headers' => ['x-tenant-id' => 'tenant-a'],
            'body' => null,
        ], $created['id']);
        self::assertSame(200, $getCode);

        $fetched = json_decode($getBody, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame($created['id'], $fetched['id']);
        self::assertSame(7, $fetched['weight']);

        $service->patch('tenant-a', $created['id'], ['name' => 'Beta', 'weight' => '9']);
        $patched = $service->get('tenant-a', $created['id']);
        self::assertIsArray($patched);
        self::assertSame('Beta', $patched['name']);
        self::assertSame(9, $patched['weight']);

        $service->delete('tenant-a', $created['id']);
        [$missingCode, , $missingBody] = $controller->get([
            'headers' => ['x-tenant-id' => 'tenant-a'],
            'body' => null,
        ], $created['id']);
        self::assertSame(404, $missingCode);
        self::assertStringContainsString('not_found', $missingBody);
    }

    public function testServiceRejectsEmptyPatchAfterNormalization(): void
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
                return $record->toArray();
            }

            public function patch(string $tenant, string $id, array $patch): void {}

            public function delete(string $tenant, string $id): void {}
        };
        $service = new TagEntityService($repo, new TagSlugPolicy($repo, new TagSlugifier()), new TagEntityPayloadNormalizer());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('validation_failed');
        $service->patch('tenant-a', 'missing', []);
    }
}
