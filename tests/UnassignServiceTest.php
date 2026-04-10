<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Infrastructure\Outbox\Tag\OutboxPublisher;
use App\Service\Core\Tag\UnassignService;
use PHPUnit\Framework\TestCase;

final class UnassignServiceTest extends TestCase
{
    public function testReturnsTagNotFoundWhenTagEntityDoesNotExist(): void
    {
        $pdo = $this->sqlite();
        $service = new UnassignService($pdo, new OutboxPublisher($pdo));

        $result = $service->unassign('demo', 'missing-tag', 'product', 'p-1');

        self::assertSame(['ok' => false, 'code' => 'tag_not_found'], $result);
    }

    public function testReturnsNotFoundWhenLinkDoesNotExistButTagDoes(): void
    {
        $pdo = $this->sqlite();
        $pdo->prepare('INSERT INTO tag_entity (tenant, id) VALUES (:tenant, :id)')->execute([
            ':tenant' => 'demo',
            ':id' => 'tag-1',
        ]);

        $service = new UnassignService($pdo, new OutboxPublisher($pdo));
        $result = $service->unassign('demo', 'tag-1', 'product', 'p-1');

        self::assertSame(['ok' => true, 'not_found' => true], $result);
    }

    private function sqlite(): \PDO
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $pdo->exec(
            'CREATE TABLE tag_entity ('
            .'tenant TEXT NOT NULL, '
            .'id TEXT NOT NULL, '
            .'PRIMARY KEY (tenant, id)'
            .')',
        );
        $pdo->exec(
            'CREATE TABLE tag_link ('
            .'tenant TEXT NOT NULL, '
            .'entity_type TEXT NOT NULL, '
            .'entity_id TEXT NOT NULL, '
            .'tag_id TEXT NOT NULL, '
            .'PRIMARY KEY (tenant, entity_type, entity_id, tag_id)'
            .')',
        );

        return $pdo;
    }
}
