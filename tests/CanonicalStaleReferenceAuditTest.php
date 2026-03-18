<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class CanonicalStaleReferenceAuditTest extends TestCase
{
    public function testRepositoryPlanDoesNotMentionRemovedCanonicalViolations(): void
    {
        $plan = (string) file_get_contents(dirname(__DIR__).'/docs/architecture/repository-production-readiness-plan.md');

        self::assertStringNotContainsString('src/Service/Tag/', $plan);
        self::assertStringNotContainsString('src/Application/Tag/', $plan);
        self::assertStringNotContainsString('src/ServiceInterface/Tag/', $plan);
    }
}
