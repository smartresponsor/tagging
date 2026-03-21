<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class StructuralCanonCleanupTest extends TestCase
{
    public function testServiceContractsLiveBesideCoreTagServices(): void
    {
        self::assertDirectoryDoesNotExist(dirname(__DIR__).'/src/ServiceInterface');
        self::assertFileExists(dirname(__DIR__).'/src/Service/Core/Tag/TagRepositoryInterface.php');
        self::assertFileExists(dirname(__DIR__).'/src/Service/Core/Tag/TagEntityRepositoryInterface.php');
        self::assertFileExists(dirname(__DIR__).'/src/Service/Core/Tag/TransactionRunnerInterface.php');
    }
}
