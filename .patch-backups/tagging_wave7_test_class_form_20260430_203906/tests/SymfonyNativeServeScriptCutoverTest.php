<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SymfonyNativeServeScriptCutoverTest extends TestCase
{
    public function testLocalServeScriptIsRetiredFromPackageSurface(): void
    {
        self::assertFileDoesNotExist(dirname(__DIR__) . '/tools/local/tag-serve.sh');
    }

    public function testContainerHostRunScriptIsRetiredFromPackageSurface(): void
    {
        self::assertFileDoesNotExist(dirname(__DIR__) . '/host/run.sh');
    }
}
