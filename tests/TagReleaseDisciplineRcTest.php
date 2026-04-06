<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagReleaseDisciplineRcTest extends TestCase
{
    public function testCiAndPreflightIncludeReleaseAssetAndOpenApiSemanticsAudits(): void
    {
        $composer = file_get_contents(__DIR__ . '/../composer.json');
        $preflight = file_get_contents(__DIR__ . '/../tools/release/tag-preflight.php');
        $ci = file_get_contents(__DIR__ . '/../.github/workflows/ci.yml');
        $ciAudit = file_get_contents(__DIR__ . '/../tools/audit/tag-ci-workflow-audit.php');

        self::assertIsString($composer);
        self::assertIsString($preflight);
        self::assertIsString($ci);
        self::assertIsString($ciAudit);

        self::assertStringContainsString('audit:release-assets', $composer);
        self::assertStringContainsString('audit:openapi-semantics', $composer);
        self::assertStringContainsString('tag-release-assets-audit.php', $preflight);
        self::assertStringContainsString('tag-openapi-semantics-audit.php', $preflight);
        self::assertStringContainsString('composer run -n audit:release-assets', $ci);
        self::assertStringContainsString('composer run -n audit:openapi-semantics', $ci);
        self::assertStringContainsString('composer run -n audit:release-assets', $ciAudit);
        self::assertStringContainsString('composer run -n audit:openapi-semantics', $ciAudit);
    }

    public function testReleaseWorkflowExistsAndPackagesRcBundle(): void
    {
        $workflow = file_get_contents(__DIR__ . '/../.github/workflows/release-rc.yml');
        self::assertIsString($workflow);

        self::assertStringContainsString("push:\n    tags:", $workflow);
        self::assertStringContainsString('composer run -n release:preflight', $workflow);
        self::assertStringContainsString('composer run -n audit:release-assets', $workflow);
        self::assertStringContainsString('composer run -n audit:openapi-semantics', $workflow);
        self::assertStringContainsString('actions/upload-artifact@v4', $workflow);
        self::assertStringContainsString('release-bundle', $workflow);
    }
}
