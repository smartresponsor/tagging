<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Tagging\Http\Api\Tag\TagAssignController;
use App\Tagging\Service\Core\TagAssignOperationInterface;
use App\Tagging\Service\Core\TagUnassignOperationInterface;
use PHPUnit\Framework\TestCase;

final class TagAssignControllerUnassignContractTest extends TestCase
{
    public function testUnassignMapsMissingTagToHttp404(): void
    {
        $controller = new TagAssignController(
            new class implements TagAssignOperationInterface {
                public function assign(
                    string $tenant,
                    string $tagId,
                    string $entityType,
                    string $entityId,
                    ?string $idemKey = null,
                ): array {
                    return ['ok' => true];
                }
            },
            new class implements TagUnassignOperationInterface {
                public function unassign(
                    string $tenant,
                    string $tagId,
                    string $entityType,
                    string $entityId,
                    ?string $idemKey = null,
                ): array {
                    return ['ok' => false, 'code' => 'tag_not_found'];
                }
            },
            ['entity_types' => ['product']],
        );

        [$status, $headers, $body] = $controller->unassign([
            'headers' => ['X-Tenant-Id' => 'demo'],
            'body' => ['entityType' => 'product', 'entityId' => 'p-1'],
        ], 'missing-tag');

        $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(404, $status);
        self::assertSame('application/json', $headers['Content-Type']);
        self::assertFalse($payload['ok']);
        self::assertSame('tag_not_found', $payload['code']);
        self::assertFalse($payload['not_found']);
        self::assertFalse($payload['duplicated']);
        self::assertFalse($payload['conflict']);
    }
}
