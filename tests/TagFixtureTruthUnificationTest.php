<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagFixtureTruthUnificationTest extends TestCase
{
    public function testFixtureScriptsUseCanonicalPhpFixtureAndCatalog(): void
    {
        $validate = file_get_contents(__DIR__.'/../tools/seed/tag-fixture-validate.php');
        $seed = file_get_contents(__DIR__.'/../tools/seed/tag-seed.php');
        $dryRun = file_get_contents(__DIR__.'/../tools/seed/tag-fixture-dry-run.php');
        $loader = file_get_contents(__DIR__.'/../tools/seed/tag-demo-fixture-loader.php');

        self::assertIsString($validate);
        self::assertIsString($seed);
        self::assertIsString($dryRun);
        self::assertIsString($loader);

        self::assertStringContainsString('tag-demo-fixture-loader.php', $validate);
        self::assertStringContainsString('tag-demo-fixture-loader.php', $seed);
        self::assertStringContainsString('tag-demo-fixture-loader.php', $dryRun);
        self::assertStringContainsString('fixtures/tag-demo-fixture.php', $loader);
        self::assertStringContainsString('fixtures/tag-demo-catalog.php', $loader);
        self::assertStringNotContainsString('tag-demo.json', $validate);
        self::assertStringNotContainsString('tag-demo.json', $seed);
        self::assertStringNotContainsString('tag-demo.json', $dryRun);
    }

    public function testFixtureValidateStillPassesAgainstCurrentCanonicalFixture(): void
    {
        $command = escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/../tools/seed/tag-fixture-validate.php');
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
        self::assertStringContainsString('tag-fixture-validate: ok', $stdout);
    }
}
