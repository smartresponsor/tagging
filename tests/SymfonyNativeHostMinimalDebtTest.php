<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SymfonyNativeHostMinimalDebtTest extends TestCase
{
    public function testHostMinimalDebtFilesAreExplicitlyTracked(): void
    {
        $root = dirname(__DIR__);

        foreach ([
            'public/index.php',
            'tag.yaml',
            'config/tag_runtime.php',
            'docs/http/http-wiring.md',
            'docs/architecture/decisions/adr-host-minimal-runtime-boundary.md',
            'host-minimal/bootstrap.php',
            'host-minimal/index.php',
        ] as $path) {
            self::assertFileExists($root . '/' . $path);
        }
    }

    public function testPublicIndexStillContainsHostMinimalDebtUntilMutationWave(): void
    {
        $content = $this->read('public/index.php');

        self::assertStringContainsString('host-minimal/index.php', $content);
        self::assertFileExists(dirname(__DIR__) . '/public/index.symfony-native.php');
    }

    public function testTagYamlStillContainsHostMinimalDebtUntilMutationWave(): void
    {
        $content = $this->read('tag.yaml');

        self::assertStringContainsString('runtime: host-minimal', $content);
    }

    public function testRuntimeProjectionStillContainsHostMinimalDebtUntilMutationWave(): void
    {
        $content = $this->read('config/tag_runtime.php');

        self::assertStringContainsString("'host-minimal'", $content);
    }

    public function testHttpWiringDocsStillContainHostMinimalDebtUntilMutationWave(): void
    {
        $content = $this->read('docs/http/http-wiring.md');

        self::assertStringContainsString('host-minimal', $content);
    }

    public function testSymfonyNativeReplacementFilesAlreadyExist(): void
    {
        $root = dirname(__DIR__);

        foreach ([
            'public/index.symfony-native.php',
            'config/routes/tagging_native.yaml',
            'migration/symfony-native-target/composer-runtime-packages.json',
            'docs/architecture/symfony-native-existing-file-rewrite-map.md',
        ] as $path) {
            self::assertFileExists($root . '/' . $path);
        }
    }

    private function read(string $relativePath): string
    {
        $content = file_get_contents(dirname(__DIR__) . '/' . $relativePath);
        self::assertIsString($content);

        return $content;
    }
}
