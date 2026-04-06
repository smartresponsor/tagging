<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagOpenApiContractTest extends TestCase
{
    public function testUnassignContractDistinguishesMissingTagFromMissingLink(): void
    {
        $contract = file_get_contents(__DIR__ . '/../contracts/http/tag-openapi.yaml');
        self::assertIsString($contract);
        self::assertStringContainsString('/tag/{id}/unassign:', $contract);
        self::assertStringContainsString("'404':", $contract);
        self::assertStringContainsString('code: tag_not_found', $contract);
        self::assertStringContainsString('link is removed or already absent', $contract);
    }

    public function testOpenApiDocumentsTenantHeaderAnd400SemanticsAcrossPublicBusinessRoutes(): void
    {
        $contract = file_get_contents(__DIR__ . '/../contracts/http/tag-openapi.yaml');
        self::assertIsString($contract);

        self::assertStringContainsString('/tag/{id}/assign:', $contract);
        self::assertStringContainsString('/tag/assignments/bulk:', $contract);
        self::assertStringContainsString('/tag/assignments/bulk-to-entity:', $contract);
        self::assertStringContainsString('/tag/assignments:', $contract);
        self::assertStringContainsString('/tag/search:', $contract);
        self::assertStringContainsString('/tag/suggest:', $contract);
        self::assertGreaterThanOrEqual(10, substr_count($contract, 'name: X-Tenant-Id'));
        self::assertStringContainsString('including `invalid_tenant` or `validation_failed`', $contract);
        self::assertStringContainsString('including `invalid_tenant`', $contract);
        self::assertStringContainsString('per-item results may include `tag_not_found`, `assign_failed`, or `validation_failed`', $contract);
    }

    public function testOpenApiDocumentsCrudSuccessStatusesAndMetaRouteHeaders(): void
    {
        $contract = file_get_contents(__DIR__ . '/../contracts/http/tag-openapi.yaml');
        self::assertIsString($contract);

        self::assertStringContainsString("/tag:\n    post:", $contract);
        self::assertStringContainsString("'201':\n          description: Entity-shaped JSON payload", $contract);
        self::assertStringContainsString("'204':\n          description: No content on successful delete", $contract);
        self::assertStringContainsString('`X-Tag-Version`', $contract);
        self::assertStringContainsString('`X-Tag-Surface-Version`', $contract);
        self::assertStringContainsString('`Cache-Control: no-store`', $contract);
    }
}
