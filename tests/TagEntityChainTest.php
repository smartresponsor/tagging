<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Application\Write\Tag\UseCase\CreateTag;
use App\Application\Write\Tag\UseCase\DeleteTag;
use App\Application\Write\Tag\UseCase\PatchTag;
use App\Http\Api\Tag\Responder\TagWriteResponder;
use App\Http\Api\Tag\TagController;
use App\Infrastructure\Persistence\Tag\PdoTagEntityRepository;
use App\Service\Core\Tag\PdoTransactionRunner;
use App\Service\Core\Tag\Slug\Slugifier;
use App\Service\Core\Tag\Slug\SlugPolicy;
use App\Service\Core\Tag\TagEntityPayloadNormalizer;
use App\Service\Core\Tag\TagEntityService;
use PHPUnit\Framework\TestCase;

final class TagEntityChainTest extends TestCase
{
    use RequiresSqlite;

    public function testEntityRepositoryServiceAndControllerChain(): void
    {
        $this->requireSqlite();

        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE tag_entity (
            id TEXT PRIMARY KEY,
            tenant TEXT NOT NULL,
            slug TEXT NOT NULL,
            name TEXT NOT NULL,
            locale TEXT NOT NULL DEFAULT "en",
            weight INTEGER NOT NULL DEFAULT 0,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        )');
        $pdo->exec('CREATE UNIQUE INDEX tag_entity_slug_uq ON tag_entity (tenant, slug)');

        $repo = new PdoTagEntityRepository($pdo);
        $slugPolicy = new SlugPolicy($pdo, new Slugifier());
        $normalizer = new TagEntityPayloadNormalizer();
        $service = new TagEntityService($repo, $slugPolicy, $normalizer);
        $tx = new PdoTransactionRunner($pdo);
        $controller = new TagController(
            $service,
            new CreateTag($repo, $slugPolicy, $tx),
            new PatchTag($repo, $tx),
            new DeleteTag($repo, $tx),
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
        $this->requireSqlite();

        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(
            'CREATE TABLE tag_entity ('
            .'id TEXT PRIMARY KEY, '
            .'tenant TEXT NOT NULL, '
            .'slug TEXT NOT NULL, '
            .'name TEXT NOT NULL, '
            .'locale TEXT NOT NULL DEFAULT "en", '
            .'weight INTEGER NOT NULL DEFAULT 0'
            .')',
        );
        $repo = new PdoTagEntityRepository($pdo);
        $service = new TagEntityService($repo, new SlugPolicy($pdo, new Slugifier()), new TagEntityPayloadNormalizer());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('validation_failed');
        $service->patch('tenant-a', 'missing', []);
    }
}
