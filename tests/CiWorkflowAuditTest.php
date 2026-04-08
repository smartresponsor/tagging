<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class CiWorkflowAuditTest extends TestCase
{
    public function testCiWorkflowAuditScriptExists(): void
    {
        self::assertFileExists(__DIR__.'/../tools/audit/tag-ci-workflow-audit.php');
    }

    public function testCiWorkflowContainsRequiredGates(): void
    {
        $content = (string) file_get_contents(__DIR__.'/../.github/workflows/ci.yml');

        self::assertStringContainsString('composer run -n audit:ci-workflow', $content);
        self::assertStringContainsString('composer run -n test:integration', $content);
        self::assertStringContainsString('composer run -n audit:snapshot-purity', $content);
        self::assertStringNotContainsString('composer run -n audit:repo-hygiene
          composer run -n audit:snapshot-purity', $content);
    }
}
