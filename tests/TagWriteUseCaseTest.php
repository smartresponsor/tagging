<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Application\Tag\Dto\CreateTagCommand;
use App\Application\Tag\Dto\DeleteTagCommand;
use App\Application\Tag\Dto\PatchTagCommand;
use App\Application\Tag\UseCase\CreateTag;
use App\Application\Tag\UseCase\DeleteTag;
use App\Application\Tag\UseCase\PatchTag;
use App\Http\Tag\Responder\TagWriteResponder;
use App\Service\Tag\Slug\Slugifier;
use App\Service\Tag\Slug\SlugPolicy;
use App\ServiceInterface\Tag\TagEntityRepositoryInterface;
use App\ServiceInterface\Tag\TransactionRunnerInterface;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use function extension_loaded;

/**
 *
 */

/**
 *
 */
final class TagWriteUseCaseTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Unit suite MUST NOT hard-fail if PDO driver is missing on the host.
        // DB-backed coverage belongs to the integration suite.
        if (!extension_loaded('pdo_pgsql')) {
            TagWriteUseCaseTest::markTestSkipped('pdo_pgsql extension is not available on this PHP runtime.');
        }
        public
        /**
         * @return void
         */
        function testCreateTagMapsUniqueViolationToConflict(): void
        {
            $repo = new class implements TagEntityRepositoryInterface {
                /**
                 * @param string $tenant
                 * @param string $id
                 * @return array|null
                 */
                public function findById(string $tenant, string $id): ?array
                {
                    return null;
                }

                /**
                 * @param string $tenant
                 * @param string $id
                 * @param string $slug
                 * @param string $name
                 * @param string $locale
                 * @param int $weight
                 * @return array
                 */
                public function create(string $tenant, string $id, string $slug, string $name, string $locale, int $weight): array
                {
                    throw new PDOException('duplicate', (int)'23505');
                }

                /**
                 * @param string $tenant
                 * @param string $id
                 * @param array $patch
                 * @return void
                 */
                public function patch(string $tenant, string $id, array $patch): void
                {
                }

                /**
                 * @param string $tenant
                 * @param string $id
                 * @return void
                 */
                public function delete(string $tenant, string $id): void
                {
                }
            };

            $tx = new class implements TransactionRunnerInterface {
                /**
                 * @param callable $callback
                 * @return mixed
                 */
                public function run(callable $callback): mixed
                {
                    return $callback();
                }
            };

            $policy = new SlugPolicy(new PDO('sqlite::memory:'), new Slugifier());
            $useCase = new CreateTag($repo, $policy, $tx);

            $result = $useCase->execute(new CreateTagCommand('tenant-a', ['name' => 'Alpha', 'slug' => 'alpha']));
            $response = (new TagWriteResponder())->respond($result);

            TagWriteUseCaseTest::assertSame(409, $response[0]);
            TagWriteUseCaseTest::assertStringContainsString('conflict', $response[2]);
        }

        public
        /**
         * @return void
         */
        function testPatchTagReturnsNotFoundWhenEntityMissing(): void
        {
            $repo = new class implements TagEntityRepositoryInterface {
                /**
                 * @param string $tenant
                 * @param string $id
                 * @return array|null
                 */
                public function findById(string $tenant, string $id): ?array
                {
                    return null;
                }

                /**
                 * @param string $tenant
                 * @param string $id
                 * @param string $slug
                 * @param string $name
                 * @param string $locale
                 * @param int $weight
                 * @return array
                 */
                public function create(string $tenant, string $id, string $slug, string $name, string $locale, int $weight): array
                {
                    return [];
                }

                /**
                 * @param string $tenant
                 * @param string $id
                 * @param array $patch
                 * @return void
                 */
                public function patch(string $tenant, string $id, array $patch): void
                {
                }

                /**
                 * @param string $tenant
                 * @param string $id
                 * @return void
                 */
                public function delete(string $tenant, string $id): void
                {
                }
            };

            $tx = new class implements TransactionRunnerInterface {
                /**
                 * @param callable $callback
                 * @return mixed
                 */
                public function run(callable $callback): mixed
                {
                    return $callback();
                }
            };

            $useCase = new PatchTag($repo, $tx);
            $result = $useCase->execute(new PatchTagCommand('tenant-a', '01ARZ3NDEKTSV4RRFFQ69G5FAV', ['name' => 'Beta']));
            $response = (new TagWriteResponder())->respond($result);

            TagWriteUseCaseTest::assertSame(404, $response[0]);
            TagWriteUseCaseTest::assertStringContainsString('not_found', $response[2]);
        }

        public
        /**
         * @return void
         */
        function testDeleteTagReturnsNoContentWhenEntityExists(): void
        {
            $repo = new class implements TagEntityRepositoryInterface {
                public bool $deleted = false;

                /**
                 * @param string $tenant
                 * @param string $id
                 * @return array|null
                 */
                public function findById(string $tenant, string $id): ?array
                {
                    return ['id' => $id, 'slug' => 'x', 'name' => 'x', 'locale' => 'en', 'weight' => 0];
                }

                /**
                 * @param string $tenant
                 * @param string $id
                 * @param string $slug
                 * @param string $name
                 * @param string $locale
                 * @param int $weight
                 * @return array
                 */
                public function create(string $tenant, string $id, string $slug, string $name, string $locale, int $weight): array
                {
                    return [];
                }

                /**
                 * @param string $tenant
                 * @param string $id
                 * @param array $patch
                 * @return void
                 */
                public function patch(string $tenant, string $id, array $patch): void
                {
                }

                /**
                 * @param string $tenant
                 * @param string $id
                 * @return void
                 */
                public function delete(string $tenant, string $id): void
                {
                    $this->deleted = true;
                }
            };

            $tx = new class implements TransactionRunnerInterface {
                /**
                 * @param callable $callback
                 * @return mixed
                 */
                public function run(callable $callback): mixed
                {
                    return $callback();
                }
            };

            $useCase = new DeleteTag($repo, $tx);
            $result = $useCase->execute(new DeleteTagCommand('tenant-a', '01ARZ3NDEKTSV4RRFFQ69G5FAV'));
            $response = (new TagWriteResponder())->respond($result);

            TagWriteUseCaseTest::assertSame(204, $response[0]);
            TagWriteUseCaseTest::assertSame('', $response[2]);
            TagWriteUseCaseTest::assertTrue($repo->deleted);
        }
    }
