<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Tagging\Entity\Core\Tag\TagLink;
use App\Tagging\Infrastructure\Outbox\Tag\TagOutboxPublisher;
use App\Tagging\Service\Core\TagEntityRepositoryInterface;
use App\Tagging\Service\Core\TagUnassignService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

final class TagUnassignServiceTest extends TestCase
{
    public function testReturnsTagNotFoundWhenTagEntityDoesNotExist(): void
    {
        $entityManager = $this->entityManagerMock();
        $tagRepo = $this->createMock(TagEntityRepositoryInterface::class);
        $tagRepo->method('findById')->willReturn(null);

        $service = new TagUnassignService($entityManager, $tagRepo, new TagOutboxPublisher($entityManager));

        $result = $service->unassign('demo', 'missing-tag', 'product', 'p-1');

        self::assertSame(['ok' => false, 'code' => 'tag_not_found'], $result);
    }

    public function testReturnsNotFoundWhenLinkDoesNotExistButTagDoes(): void
    {
        $entityManager = $this->entityManagerMock();
        $tagRepo = $this->createMock(TagEntityRepositoryInterface::class);
        $tagRepo->method('findById')->willReturn(['id' => 'tag-1']);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->with([
            'tenant' => 'demo',
            'entityType' => 'product',
            'entityId' => 'p-1',
            'tagId' => 'tag-1',
        ])->willReturn(null);
        $entityManager->method('getRepository')->with(TagLink::class)->willReturn($repo);

        $service = new TagUnassignService($entityManager, $tagRepo, new TagOutboxPublisher($entityManager));
        $result = $service->unassign('demo', 'tag-1', 'product', 'p-1');

        self::assertSame(['ok' => true, 'not_found' => true], $result);
    }

    private function entityManagerMock(): EntityManagerInterface
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $connection = $this->createMock(Connection::class);
        $connection->method('isTransactionActive')->willReturn(true);

        $entityManager->method('getConnection')->willReturn($connection);

        return $entityManager;
    }
}
