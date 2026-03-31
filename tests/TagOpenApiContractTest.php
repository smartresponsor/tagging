<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
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
}
