<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagReadinessPlanTruthTest extends TestCase
{
    public function testReadinessPlanReflectsCurrentRuntimeAndDeliveryReality(): void
    {
        $doc = file_get_contents(__DIR__ . '/../docs/architecture/repository-production-readiness-plan.md');
        self::assertIsString($doc);

        self::assertStringContainsString('contracts/http/tag-openapi.yaml', $doc);
        self::assertStringContainsString('Cruding', $doc);
        self::assertStringContainsString('bulk assignment routes', $doc);
        self::assertStringContainsString('authoritative', $doc);
        self::assertStringContainsString('`total`', $doc);
        self::assertStringContainsString('CI now runs on push/PR', $doc);
        self::assertStringContainsString('fixtures/tag-demo.json', $doc);
        self::assertStringContainsString('hosted Symfony package', $doc);
        self::assertStringNotContainsString('centered on a shipped `host-minimal` runtime', $doc);

        self::assertStringNotContainsString('there is no local `master` branch to check out', $doc);
        self::assertStringNotContainsString('GitHub Actions workflow exists only for manual SLO gate execution', $doc);
        self::assertStringNotContainsString('visible split of namespaces (`Domain`, `Service`, `Infra`, `Http`, `Data`)', $doc);
        self::assertStringNotContainsString('src/ServiceInterface', $doc);
    }
}
