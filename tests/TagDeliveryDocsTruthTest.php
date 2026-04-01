<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagDeliveryDocsTruthTest extends TestCase
{
    public function testPublicReadyChecklistMatchesCurrentPublicShell(): void
    {
        $checklist = file_get_contents(__DIR__ . '/../docs/public/tag-public-ready-checklist.md');

        self::assertIsString($checklist);
        self::assertStringContainsString('/tag/assignments/bulk', $checklist);
        self::assertStringContainsString('/tag/assignments/bulk-to-entity', $checklist);
        self::assertStringContainsString('authoritative `total`', $checklist);
        self::assertStringContainsString('tag_not_found', $checklist);
        self::assertStringNotContainsString('bulk assignment routes', $checklist);
    }

    public function testReleasePortraitAndAdminGuideDescribeCurrentSurface(): void
    {
        $portrait = file_get_contents(__DIR__ . '/../docs/release/tag-release-grade-portrait.md');
        $adminGuide = file_get_contents(__DIR__ . '/../docs/admin/user-guide.md');

        self::assertIsString($portrait);
        self::assertIsString($adminGuide);
        self::assertStringContainsString('/tag/assignments/bulk', $portrait);
        self::assertStringContainsString('/tag/assignments/bulk-to-entity', $portrait);
        self::assertStringContainsString('authoritative `total`', $portrait);
        self::assertStringContainsString('/tag/assignments/bulk', $adminGuide);
        self::assertStringContainsString('/tag/assignments/bulk-to-entity', $adminGuide);
        self::assertStringContainsString('tag_not_found', $adminGuide);
    }

    public function testReleaseGradePortraitAuditPassesForCurrentDocs(): void
    {
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/../tools/audit/tag-release-grade-portrait-audit.php');
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = proc_open($command, $descriptorSpec, $pipes, dirname(__DIR__));
        self::assertIsResource($process);
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]) ?: '';
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[2]);
        $exit = proc_close($process);

        self::assertSame(0, $exit, $stderr);
        self::assertStringContainsString('OK', $stdout);
    }
}
