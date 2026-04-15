<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SymfonyNativeSurfaceTest extends TestCase
{
    public function testPublicRuntimeTruthIsSymfonyNative(): void
    {
        $tagYaml = file_get_contents(dirname(__DIR__) . '/tag.yaml');
        self::assertIsString($tagYaml);
        self::assertStringContainsString('runtime: symfony-native', $tagYaml);
        self::assertStringNotContainsString('runtime: host-minimal', $tagYaml);
    }

    public function testPublicIndexUsesSymfonyKernelEntry(): void
    {
        $publicIndex = file_get_contents(dirname(__DIR__) . '/public/index.php');
        self::assertIsString($publicIndex);

        self::assertStringContainsString('use App\\Kernel;', $publicIndex);
        self::assertStringContainsString("require dirname(__DIR__) . '/config/bootstrap.php';", $publicIndex);
        self::assertStringNotContainsString('host-minimal/index.php', $publicIndex);
    }

    public function testContractStillDocumentsStatusAndSurfaceRoutes(): void
    {
        $contract = file_get_contents(dirname(__DIR__) . '/contracts/http/tag-openapi.yaml');
        self::assertIsString($contract);
        self::assertStringContainsString('/tag/_status', $contract);
        self::assertStringContainsString('/tag/_surface', $contract);
        self::assertStringNotContainsString('/tag/{id}/synonym', $contract);
        self::assertStringNotContainsString('/tag/redirect/{fromId}', $contract);
    }
}
