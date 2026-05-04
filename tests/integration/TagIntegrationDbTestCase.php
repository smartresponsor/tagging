<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Tests\TagRequiresSqlite;
use Tests\Support\TagDoctrineEntityManagerFactory;
use PHPUnit\Framework\TestCase;

abstract class TagIntegrationDbTestCase extends TestCase
{
    use TagRequiresSqlite;

    protected ?EntityManagerInterface $entityManager = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requireSqlite();
        $this->entityManager = TagDoctrineEntityManagerFactory::create();
    }

    protected function entityManager(): EntityManagerInterface
    {
        if (!$this->entityManager instanceof EntityManagerInterface) {
            throw new \RuntimeException('Integration entity manager is not initialized.');
        }

        return $this->entityManager;
    }
}
