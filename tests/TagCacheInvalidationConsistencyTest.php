<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Tagging\Application\Write\Tag\Dto\CreateTagCommand;
use App\Tagging\Application\Write\Tag\Dto\DeleteTagCommand;
use App\Tagging\Application\Write\Tag\Dto\PatchTagCommand;
use App\Tagging\Application\Write\Tag\UseCase\CreateTag;
use App\Tagging\Application\Write\Tag\UseCase\DeleteTag;
use App\Tagging\Application\Write\Tag\UseCase\PatchTag;
use App\Tagging\Cache\Store\Tag\SearchCache;
use App\Tagging\Cache\Store\Tag\SuggestCache;
use App\Tagging\Service\Core\Tag\Record\TagEntityCreateRecord;
use App\Tagging\Service\Core\Tag\Slug\Slugifier;
use App\Tagging\Service\Core\Tag\Slug\SlugPolicy;
use App\Tagging\Service\Core\Tag\TagEntityRepositoryInterface;
use App\Tagging\Service\Core\Tag\TransactionRunnerInterface;
use PHPUnit\Framework\TestCase;

final class TagCacheInvalidationConsistencyTest extends TestCase
{
    use RequiresSqlite;

    public function testSearchAndSuggestCachesUseSameFileStorePattern(): void
    {
        $baseDir = sys_get_temp_dir() . '/tag-cache-' . bin2hex(random_bytes(4));
        mkdir($baseDir, 0777, true);

        $search = new SearchCache($baseDir . '/search', 60);
        $suggest = new SuggestCache($baseDir . '/suggest', 60);

        $search->set('tenant-a', 'Priority', 10, 0, ['items' => [['slug' => 'priority']]]);
        $suggest->set('tenant-a', 'Priority', 10, ['items' => [['slug' => 'priority', 'name' => 'Priority']]]);

        self::assertTrue($search->get('tenant-a', 'priority', 10, 0)['hit']);
        self::assertTrue($suggest->get('tenant-a', 'priority', 10)['hit']);

        self::assertCount(1, glob($baseDir . '/search/search__tenant-a__*.json') ?: []);
        self::assertCount(1, glob($baseDir . '/suggest/suggest__tenant-a__*.json') ?: []);
    }

    public function testCreatePatchAndDeleteInvalidateBothQueryCachesForTenant(): void
    {
        $this->requireSqlite();

        $baseDir = sys_get_temp_dir() . '/tag-invalidation-' . bin2hex(random_bytes(4));
        mkdir($baseDir, 0777, true);

        $repo = new class implements TagEntityRepositoryInterface {
            /** @var array<string,array<string,array<string,mixed>>> */
            private array $rows = [];

            public function findById(string $tenant, string $id): ?array
            {
                return $this->rows[$tenant][$id] ?? null;
            }

            public function create(string $tenant, TagEntityCreateRecord $record): array
            {
                return $this->rows[$tenant][$record->id] = [
                    'id' => $record->id,
                    'slug' => $record->slug,
                    'name' => $record->name,
                    'locale' => $record->locale,
                    'weight' => $record->weight,
                ];
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

        $tx = new class implements TransactionRunnerInterface {
            public function run(callable $callback): mixed
            {
                return $callback();
            }
        };
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE tag_entity (tenant TEXT NOT NULL, slug TEXT NOT NULL)');
        $policy = new SlugPolicy($pdo, new Slugifier());

        $search = new SearchCache($baseDir . '/search', 60);
        $suggest = new SuggestCache($baseDir . '/suggest', 60);
        $search->set('tenant-a', 'alpha', 10, 0, ['items' => [['slug' => 'alpha']]]);
        $suggest->set('tenant-a', 'alpha', 10, ['items' => [['slug' => 'alpha', 'name' => 'Alpha']]]);

        $create = new CreateTag($repo, $policy, $tx, $search, $suggest);
        $created = $create->execute(new CreateTagCommand('tenant-a', ['name' => 'Alpha']));
        self::assertTrue($created->ok);
        $id = (string) ($created->payload['id'] ?? $created->data['id'] ?? '');
        self::assertNotSame('', $id);
        self::assertFalse($search->get('tenant-a', 'alpha', 10, 0)['hit']);
        self::assertFalse($suggest->get('tenant-a', 'alpha', 10)['hit']);

        $search->set('tenant-a', 'alpha', 10, 0, ['items' => [['slug' => 'alpha']]]);
        $suggest->set('tenant-a', 'alpha', 10, ['items' => [['slug' => 'alpha', 'name' => 'Alpha']]]);
        $patch = new PatchTag($repo, $tx, $search, $suggest);
        self::assertTrue($patch->execute(new PatchTagCommand('tenant-a', $id, ['name' => 'Alpha 2']))->ok);
        self::assertFalse($search->get('tenant-a', 'alpha', 10, 0)['hit']);
        self::assertFalse($suggest->get('tenant-a', 'alpha', 10)['hit']);

        $search->set('tenant-a', 'alpha', 10, 0, ['items' => [['slug' => 'alpha']]]);
        $suggest->set('tenant-a', 'alpha', 10, ['items' => [['slug' => 'alpha', 'name' => 'Alpha']]]);
        $delete = new DeleteTag($repo, $tx, $search, $suggest);
        self::assertTrue($delete->execute(new DeleteTagCommand('tenant-a', $id))->ok);
        self::assertFalse($search->get('tenant-a', 'alpha', 10, 0)['hit']);
        self::assertFalse($suggest->get('tenant-a', 'alpha', 10)['hit']);
    }

    public function testLegacyServiceCacheTreeIsGone(): void
    {
        self::assertDirectoryDoesNotExist(dirname(__DIR__) . '/src/Service/Cache');
    }
}
