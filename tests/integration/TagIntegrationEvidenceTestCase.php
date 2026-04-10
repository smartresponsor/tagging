<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Http\Api\Tag\AssignController;
use App\Infrastructure\Outbox\Tag\OutboxPublisher;
use App\Infrastructure\ReadModel\Tag\TagReadModel;
use App\Service\Core\Tag\AssignService;
use App\Service\Core\Tag\IdempotencyStore;
use App\Service\Core\Tag\UnassignService;

abstract class TagIntegrationEvidenceTestCase extends IntegrationDbTestCase
{
    protected function insertTag(
        \PDO $pdo,
        string $tenant,
        string $id,
        string $slug,
        string $name,
        int $weight = 0,
        string $locale = 'en',
    ): void {
        $stmt = $pdo->prepare(
            'INSERT INTO tag_entity '
            . '(id, tenant, slug, name, locale, weight) '
            . 'VALUES (:id, :tenant, :slug, :name, :locale, :weight)',
        );
        $stmt->execute([
            ':id' => $id,
            ':tenant' => $tenant,
            ':slug' => $slug,
            ':name' => $name,
            ':locale' => $locale,
            ':weight' => $weight,
        ]);
    }

    protected function readModel(\PDO $pdo): TagReadModel
    {
        return new TagReadModel($pdo);
    }

    protected function assignService(\PDO $pdo): AssignService
    {
        return new AssignService($pdo, new OutboxPublisher($pdo), new IdempotencyStore($pdo));
    }

    protected function unassignService(\PDO $pdo): UnassignService
    {
        return new UnassignService($pdo, new OutboxPublisher($pdo), new IdempotencyStore($pdo));
    }

    protected function assignController(\PDO $pdo): AssignController
    {
        return new AssignController(
            $this->assignService($pdo),
            $this->unassignService($pdo),
            ['entity_types' => ['*']],
        );
    }

    protected function decodeBody(string $body): array
    {
        $decoded = json_decode($body, true);
        self::assertIsArray($decoded);

        return $decoded;
    }
}
