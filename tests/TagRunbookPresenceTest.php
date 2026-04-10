<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagRunbookPresenceTest extends TestCase
{
    public function testRunbookExistsAndCoversCriticalOpsSteps(): void
    {
        $doc = file_get_contents(__DIR__ . '/../docs/ops/runbook.md');

        self::assertIsString($doc);
        self::assertStringContainsString('db:migrate', $doc);
        self::assertStringContainsString('demo:seed', $doc);
        self::assertStringContainsString('smoke:runtime', $doc);
        self::assertStringContainsString('/tag/_status', $doc);
        self::assertStringContainsString('/tag/_surface', $doc);
        self::assertStringContainsString('rollback', $doc);
    }
}
