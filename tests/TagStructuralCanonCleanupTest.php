<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagStructuralCanonCleanupTest extends TestCase
{
    public function testRepositoryContractsLiveInRepositoryInterfaceRole(): void
    {
        self::assertDirectoryDoesNotExist(dirname(__DIR__) . '/src/ServiceInterface');
        self::assertFileExists(dirname(__DIR__) . '/src/RepositoryInterface/TagRepositoryInterface.php');
        self::assertFileExists(dirname(__DIR__) . '/src/RepositoryInterface/TagCrudRepositoryInterface.php');
        self::assertFileExists(dirname(__DIR__) . '/src/RepositoryInterface/TagTransactionRunnerInterface.php');
    }
}
