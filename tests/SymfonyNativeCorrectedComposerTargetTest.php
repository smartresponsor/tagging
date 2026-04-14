<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SymfonyNativeCorrectedComposerTargetTest extends TestCase
{
    public function testCorrectedComposerReplacementTargetExists(): void
    {
        self::assertFileExists(dirname(__DIR__) . '/migration/symfony-native-target/composer.symfony-native.corrected.json');
    }

    public function testCorrectedComposerReplacementTargetUsesValidPsr4Keys(): void
    {
        $decoded = $this->readJson();

        self::assertSame('src/', $decoded['autoload']['psr-4']['App\\'] ?? null);
        self::assertSame('tests/', $decoded['autoload-dev']['psr-4']['Tests\\'] ?? null);
        self::assertSame('tests/integration/', $decoded['autoload-dev']['psr-4']['Tests\\Integration\\'] ?? null);
        self::assertSame('tests/e2e/', $decoded['autoload-dev']['psr-4']['Tests\\E2E\\'] ?? null);
        self::assertSame('tests/panther/', $decoded['autoload-dev']['psr-4']['Tests\\Panther\\'] ?? null);
    }

    public function testCorrectedComposerReplacementTargetContainsSymfonyEightRuntimePackages(): void
    {
        $decoded = $this->readJson();
        $require = $decoded['require'] ?? [];
        self::assertIsArray($require);

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
            self::assertSame('^8.0', $require[$package] ?? null, sprintf('Missing Symfony 8 runtime package %s', $package));
        }
    }

    /** @return array<string,mixed> */
    private function readJson(): array
    {
        $content = file_get_contents(dirname(__DIR__) . '/migration/symfony-native-target/composer.symfony-native.corrected.json');
        self::assertIsString($content);

        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);

        return $decoded;
    }
}
