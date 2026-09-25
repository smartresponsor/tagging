<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Repository\Outbox;

use App\Tagging\Entity\Tag\TagOutboxEventEntity;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TagOutboxPublisher
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    /** @param array<string,mixed> $payload */
    public function publish(string $tenant, string $topic, array $payload): void
    {
        $this->entityManager->persist(new TagOutboxEventEntity(
            tenant: $tenant,
            topic: $topic,
            payload: $payload,
        ));
        $this->entityManager->flush();
    }
}
