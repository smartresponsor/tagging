<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagSdkAndDemoTruthTest extends TestCase
{
    public function testSdkClientsExposeBulkOperations(): void
    {
        $phpSdk = file_get_contents(__DIR__ . '/../sdk/php/tag/Client.php');
        $tsSdk = file_get_contents(__DIR__ . '/../sdk/ts/tag/client.ts');

        self::assertIsString($phpSdk);
        self::assertIsString($tsSdk);
        self::assertStringContainsString('function bulkAssignments', $phpSdk);
        self::assertStringContainsString('function assignBulkToEntity', $phpSdk);
        self::assertStringContainsString('/tag/assignments/bulk', $phpSdk);
        self::assertStringContainsString('/tag/assignments/bulk-to-entity', $phpSdk);
        self::assertStringContainsString('bulkAssignments(body', $tsSdk);
        self::assertStringContainsString('assignBulkToEntity(body', $tsSdk);
    }

    public function testFinalDemoPackAndSdkReadmeDescribeCurrentSurface(): void
    {
        $finalPack = file_get_contents(__DIR__ . '/../docs/demo/tag-final-demo-pack.md');
        $sdkReadme = file_get_contents(__DIR__ . '/../sdk/README.md');

        self::assertIsString($finalPack);
        self::assertIsString($sdkReadme);
        self::assertStringContainsString('/tag/assignments/bulk', $finalPack);
        self::assertStringContainsString('/tag/assignments/bulk-to-entity', $finalPack);
        self::assertStringContainsString('tag_not_found', $finalPack);
        self::assertStringContainsString('authoritative `total`', $finalPack);
        self::assertStringContainsString('bulkAssignments()', $sdkReadme);
        self::assertStringContainsString('assignBulkToEntity()', $sdkReadme);
        self::assertStringContainsString('flat payloads', $sdkReadme);
    }

    public function testSdkAndDemoAuditsStillPassTheirTruthChecks(): void
    {
        $sdkAudit = $this->runAudit('tag-sdk-audit.php');
        $demoAudit = $this->runAudit('tag-demo-truth-pack-audit.php');

        self::assertSame(0, $sdkAudit['exit']);
        self::assertStringContainsString('tag-sdk-audit: ok', $sdkAudit['stdout']);
        self::assertSame(0, $demoAudit['exit']);
        self::assertStringContainsString('OK', $demoAudit['stdout']);
    }

    /** @return array{exit:int,stdout:string,stderr:string} */
    private function runAudit(string $script): array
    {
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/../tools/audit/' . $script);
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

        return ['exit' => $exit, 'stdout' => $stdout, 'stderr' => $stderr];
    }
}
