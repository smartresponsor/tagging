<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagApiErrorCatalogTruthTest extends TestCase
{
    public function testErrorCatalogDocumentsCurrentAssignmentTransportCodes(): void
    {
        $doc = file_get_contents(__DIR__ . '/../docs/api/error-catalog.md');
        self::assertIsString($doc);

        self::assertStringContainsString('`invalid_tenant`', $doc);
        self::assertStringContainsString('`validation_failed`', $doc);
        self::assertStringContainsString('`tag_not_found`', $doc);
        self::assertStringContainsString('`idempotency_conflict`', $doc);
        self::assertStringContainsString('`assign_failed`', $doc);
        self::assertStringContainsString('`unassign_failed`', $doc);
        self::assertStringContainsString('If the tag exists but the entity link is already absent', $doc);
        self::assertStringContainsString('contracts/http/tag-openapi.yaml', $doc);
    }
}
