<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SymfonyNativeComposerDeltaBaselineTest extends TestCase
{
    public function testComposerRuntimePackageTargetExists(): void
    {
        self::assertFileExists(dirname(__DIR__) . '/migration/symfony-native-target/composer-runtime-packages.json');
    }

    public function testComposerRuntimePackageTargetIsReadableJson(): void
    {
        $content = file_get_contents(dirname(__DIR__) . '/migration/symfony-native-target/composer-runtime-packages.json');
        self::assertIsString($content);

        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);
        self::assertSame('planned_until_composer_rewrite', $decoded['status'] ?? null);
    }

    public function testComposerRuntimePackageTargetContainsRequiredSymfonyPackages(): void
    {
        $content = file_get_contents(dirname(__DIR__) . '/migration/symfony-native-target/composer-runtime-packages.json');
        self::assertIsString($content);

        foreach ([
            'symfony/framework-bundle',
            'symfony/http-kernel',
            'symfony/dependency-injection',
            'symfony/config',
            'symfony/routing',
            'symfony/console',
            'symfony/dotenv',
            'symfony/yaml',
        ] as $package) {
            self::assertStringContainsString($package, $content);
        }
    }
}
