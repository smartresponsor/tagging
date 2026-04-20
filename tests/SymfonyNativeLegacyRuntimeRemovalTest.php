<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SymfonyNativeLegacyRuntimeRemovalTest extends TestCase
{
    public function testHostMinimalSourceTreeIsNoLongerPresent(): void
    {
        $root = dirname(__DIR__);

        self::assertFileDoesNotExist($root . '/src/HostMinimal/Container/HostMinimalContainer.php');
        self::assertFileDoesNotExist($root . '/src/HostMinimal/Container/HostMinimalRuntimeConfig.php');
    }

    public function testHostMinimalEngineFilesAreNoLongerPresent(): void
    {
        $root = dirname(__DIR__);

        foreach ([
            'host-minimal/autoload.php',
            'host-minimal/bootstrap.php',
            'host-minimal/cors.php',
            'host-minimal/index.php',
            'host-minimal/route.php',
        ] as $path) {
            self::assertFileDoesNotExist($root . '/' . $path, $path);
        }
    }

    public function testActiveSourceFilesDoNotReferenceHostMinimalNamespace(): void
    {
        $root = dirname(__DIR__);
        $sources = iterator_to_array(new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root . '/src', \FilesystemIterator::SKIP_DOTS),
        ));

        foreach ($sources as $file) {
            if (!$file instanceof \SplFileInfo || 'php' !== $file->getExtension()) {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            self::assertIsString($content);
            self::assertStringNotContainsString('App\Tagging\\HostMinimal\\', $content, $file->getPathname());
        }
    }
}
