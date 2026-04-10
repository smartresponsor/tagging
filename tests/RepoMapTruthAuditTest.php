<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class RepoMapTruthAuditTest extends TestCase
{
    public function testRepoMapReflectsCurrentCanonicalLayout(): void
    {
        $repoMap = file_get_contents(__DIR__ . '/../repo-map.md');
        self::assertIsString($repoMap);

        self::assertStringContainsString('MANIFEST.json', $repoMap);
        self::assertStringContainsString('tag.yaml', $repoMap);
        self::assertStringContainsString('fixtures/', $repoMap);
        self::assertStringContainsString('public/', $repoMap);
        self::assertStringContainsString('sdk/', $repoMap);
        self::assertStringContainsString('src/Infrastructure/ReadModel/Tag/', $repoMap);
        self::assertStringContainsString('src/Service/Core/Tag/', $repoMap);
        self::assertStringNotContainsString('src/ServiceInterface/', $repoMap);
        self::assertStringNotContainsString('src/Domain/', $repoMap);
        self::assertStringNotContainsString('src/Infra/', $repoMap);
        $treeSection = explode('Snapshot policy:', $repoMap, 2)[0] ?? $repoMap;
        self::assertStringNotContainsString('fixtures/tag-demo.json', $treeSection);
        self::assertStringContainsString(
            'Retired legacy artifacts such as `fixtures/tag-demo.json` must not remain in the repository',
            $repoMap,
        );
        self::assertStringContainsString('docs/public/', $repoMap);
        self::assertStringContainsString('docs/release/', $repoMap);
    }
}
