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
use App\Tagging\Cache\Store\Tag\TagSearchCache;
use App\Tagging\Cache\Store\Tag\TagSuggestCache;
use App\Tagging\Service\Core\Record\TagEntityCreateRecord;
use App\Tagging\Service\Core\Slug\TagSlugifier;
use App\Tagging\Service\Core\Slug\TagSlugPolicy;
use App\Tagging\Service\Core\TagEntityRepositoryInterface;
use App\Tagging\Service\Core\TagTransactionRunnerInterface;
use PHPUnit\Framework\TestCase;

final class TagCacheInvalidationConsistencyTest extends TestCase
{
    public function testSearchAndSuggestCachesUseSameFileStorePattern(): void
    {
        $baseDir = sys_get_temp_dir() . '/tag-cache-' . bin2hex(random_bytes(4));
        mkdir($baseDir, 0777, true);

        $search = new TagSearchCache($baseDir . '/search', 60);
        $suggest = new TagSuggestCache($baseDir . '/suggest', 60);

        $search->set('tenant-a', 'Priority', 10, 0, ['items' => [['slug' => 'priority']]]);
        $suggest->set('tenant-a', 'Priority', 10, ['items' => [['slug' => 'priority', 'name' => 'Priority']]]);

        self::assertTrue($search->get('tenant-a', 'priority', 10, 0)['hit']);
        self::assertTrue($suggest->get('tenant-a', 'priority', 10)['hit']);

        self::assertCount(1, glob($baseDir . '/search/search__tenant-a__*.json') ?: []);
        self::assertCount(1, glob($baseDir . '/suggest/suggest__tenant-a__*.json') ?: []);
    }

    public function testCreatePatchAndDeleteInvalidateBothQueryCachesForTenant(): void
    {
        $baseDir = sys_get_temp_dir() . '/tag-invalidation-' . bin2hex(random_bytes(4));
        mkdir($baseDir, 0777, true);

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

        $tx = new class implements TagTransactionRunnerInterface {
            public function run(callable $callback): mixed
            {
                return $callback();
            }
        };

        $policy = new TagSlugPolicy($repo, new TagSlugifier());

        $search = new TagSearchCache($baseDir . '/search', 60);
        $suggest = new TagSuggestCache($baseDir . '/suggest', 60);
        $search->set('tenant-a', 'alpha', 10, 0, ['items' => [['slug' => 'alpha']]]);
        $suggest->set('tenant-a', 'alpha', 10, ['items' => [['slug' => 'alpha', 'name' => 'Alpha']]]);

        $create = new TagCreateUseCase($repo, $policy, $tx, $search, $suggest);
        $created = $create->execute(new TagCreateCommand('tenant-a', ['name' => 'Alpha']));
        self::assertTrue($created->ok);
        $id = (string) ($created->payload['id'] ?? $created->data['id'] ?? '');
        self::assertNotSame('', $id);
        self::assertFalse($search->get('tenant-a', 'alpha', 10, 0)['hit']);
        self::assertFalse($suggest->get('tenant-a', 'alpha', 10)['hit']);

        $search->set('tenant-a', 'alpha', 10, 0, ['items' => [['slug' => 'alpha']]]);
        $suggest->set('tenant-a', 'alpha', 10, ['items' => [['slug' => 'alpha', 'name' => 'Alpha']]]);
        $patch = new TagPatchUseCase($repo, $tx, $search, $suggest);
        self::assertTrue($patch->execute(new TagPatchCommand('tenant-a', $id, ['name' => 'Alpha 2']))->ok);
        self::assertFalse($search->get('tenant-a', 'alpha', 10, 0)['hit']);
        self::assertFalse($suggest->get('tenant-a', 'alpha', 10)['hit']);

        $search->set('tenant-a', 'alpha', 10, 0, ['items' => [['slug' => 'alpha']]]);
        $suggest->set('tenant-a', 'alpha', 10, ['items' => [['slug' => 'alpha', 'name' => 'Alpha']]]);
        $delete = new TagDeleteUseCase($repo, $tx, $search, $suggest);
        self::assertTrue($delete->execute(new TagDeleteCommand('tenant-a', $id))->ok);
        self::assertFalse($search->get('tenant-a', 'alpha', 10, 0)['hit']);
        self::assertFalse($suggest->get('tenant-a', 'alpha', 10)['hit']);
    }

    public function testLegacyServiceCacheTreeIsGone(): void
    {
        self::assertDirectoryDoesNotExist(dirname(__DIR__) . '/src/Service/Cache');
    }
}
