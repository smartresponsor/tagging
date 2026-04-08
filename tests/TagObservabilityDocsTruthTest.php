<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagObservabilityDocsTruthTest extends TestCase
{
    public function testObservabilityDocDoesNotPromiseNonexistentMetricsRoute(): void
    {
        $doc = file_get_contents(__DIR__.'/../docs/ops/observability.md');
        self::assertIsString($doc);

        self::assertStringContainsString('there is **no shipped `/tag/_metrics` route**', $doc);
        self::assertStringContainsString('`Observe` wraps live host-minimal dispatch', $doc);
        self::assertStringContainsString('`GET /tag/_status`', $doc);
        self::assertStringContainsString('`GET /tag/_surface`', $doc);
        self::assertStringNotContainsString('Export /tag/_metrics in Prometheus', $doc);
    }
}
