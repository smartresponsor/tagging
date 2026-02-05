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
use App\Service\Tag\Slug\SlugPolicy;
use App\Service\Tag\Slug\Slugifier;
use App\ServiceInterface\Tag\TagEntityRepositoryInterface;
use App\ServiceInterface\Tag\TransactionRunnerInterface;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;

final class TagWriteUseCaseTest extends TestCase
{
    public function testCreateTagMapsUniqueViolationToConflict(): void
    {
        $repo = new class implements TagEntityRepositoryInterface {
            public function findById(string $tenant, string $id): ?array { return null; }
            public function create(string $tenant, string $id, string $slug, string $name, string $locale, int $weight): array
            {
                throw new PDOException('duplicate', '23505');
            }
            public function patch(string $tenant, string $id, array $patch): void {}
            public function delete(string $tenant, string $id): void {}
        };

        $tx = new class implements TransactionRunnerInterface {
            public function run(callable $callback): mixed { return $callback(); }
        };

        $policy = new SlugPolicy(new PDO('sqlite::memory:'), new Slugifier());
        $useCase = new CreateTag($repo, $policy, $tx);

        $result = $useCase->execute(new CreateTagCommand('tenant-a', ['name' => 'Alpha', 'slug' => 'alpha']));
        $response = (new TagWriteResponder())->respond($result);

        $this->assertSame(409, $response[0]);
        $this->assertStringContainsString('conflict', $response[2]);
    }

    public function testPatchTagReturnsNotFoundWhenEntityMissing(): void
    {
        $repo = new class implements TagEntityRepositoryInterface {
            public function findById(string $tenant, string $id): ?array { return null; }
            public function create(string $tenant, string $id, string $slug, string $name, string $locale, int $weight): array { return []; }
            public function patch(string $tenant, string $id, array $patch): void {}
            public function delete(string $tenant, string $id): void {}
        };

        $tx = new class implements TransactionRunnerInterface {
            public function run(callable $callback): mixed { return $callback(); }
        };

        $useCase = new PatchTag($repo, $tx);
        $result = $useCase->execute(new PatchTagCommand('tenant-a', '01ARZ3NDEKTSV4RRFFQ69G5FAV', ['name' => 'Beta']));
        $response = (new TagWriteResponder())->respond($result);

        $this->assertSame(404, $response[0]);
        $this->assertStringContainsString('not_found', $response[2]);
    }

    public function testDeleteTagReturnsNoContentWhenEntityExists(): void
    {
        $repo = new class implements TagEntityRepositoryInterface {
            public bool $deleted = false;
            public function findById(string $tenant, string $id): ?array { return ['id' => $id, 'slug' => 'x', 'name' => 'x', 'locale' => 'en', 'weight' => 0]; }
            public function create(string $tenant, string $id, string $slug, string $name, string $locale, int $weight): array { return []; }
            public function patch(string $tenant, string $id, array $patch): void {}
            public function delete(string $tenant, string $id): void { $this->deleted = true; }
        };

        $tx = new class implements TransactionRunnerInterface {
            public function run(callable $callback): mixed { return $callback(); }
        };

        $useCase = new DeleteTag($repo, $tx);
        $result = $useCase->execute(new DeleteTagCommand('tenant-a', '01ARZ3NDEKTSV4RRFFQ69G5FAV'));
        $response = (new TagWriteResponder())->respond($result);

        $this->assertSame(204, $response[0]);
        $this->assertSame('', $response[2]);
        $this->assertTrue($repo->deleted);
    }
}
