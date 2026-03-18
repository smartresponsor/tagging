<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class RepoMapTruthAuditTest extends TestCase
{
    public function testRepoMapReflectsCurrentCanonicalLayout(): void
    {
        $repoMap = file_get_contents(__DIR__.'/../repo-map.md');
        self::assertIsString($repoMap);

        self::assertStringContainsString('src/Infrastructure/ReadModel/Tag/', $repoMap);
        self::assertStringContainsString('src/ServiceInterface/Core/Tag/', $repoMap);
        self::assertStringNotContainsString('src/Domain/', $repoMap);
        self::assertStringNotContainsString('src/Infra/', $repoMap);
        self::assertStringContainsString('docs/public/', $repoMap);
        self::assertStringContainsString('docs/release/', $repoMap);
    }
}
