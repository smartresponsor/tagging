<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class ComposerIntegrityAuditTest extends TestCase
{
    public function testComposerConfigurationContainsInstallAndTestGates(): void
    {
        $composer = json_decode((string) file_get_contents(__DIR__.'/../composer.json'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('src/', $composer['autoload']['psr-4']['App\\'] ?? null);
        self::assertArrayHasKey('phpunit/phpunit', $composer['require-dev'] ?? []);
        self::assertArrayHasKey('audit:composer-integrity', $composer['scripts'] ?? []);
        self::assertArrayHasKey('test', $composer['scripts'] ?? []);
        self::assertArrayHasKey('test:unit', $composer['scripts'] ?? []);
        self::assertArrayHasKey('gate:install-test', $composer['scripts'] ?? []);
    }
}
