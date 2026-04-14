<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SymfonyNativeComposerReplacementTargetTest extends TestCase
{
    public function testComposerReplacementTargetExists(): void
    {
        self::assertFileExists(dirname(__DIR__) . '/migration/symfony-native-target/composer.symfony-native.json');
    }

    public function testComposerReplacementTargetIsReadableJson(): void
    {
        $content = file_get_contents(dirname(__DIR__) . '/migration/symfony-native-target/composer.symfony-native.json');
        self::assertIsString($content);

        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);
        self::assertSame('smartresponsor/tag', $decoded['name'] ?? null);
        self::assertSame('library', $decoded['type'] ?? null);
    }

    public function testComposerReplacementTargetContainsSymfonyRuntimePackages(): void
    {
        $content = file_get_contents(dirname(__DIR__) . '/migration/symfony-native-target/composer.symfony-native.json');
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

    public function testComposerReplacementTargetContainsSymfonyScripts(): void
    {
        $content = file_get_contents(dirname(__DIR__) . '/migration/symfony-native-target/composer.symfony-native.json');
        self::assertIsString($content);

        foreach ([
            'symfony:console',
            'symfony:routes',
            'symfony:container',
        ] as $scriptName) {
            self::assertStringContainsString($scriptName, $content);
        }
    }
}
