<?php

// Copyright (c) 2026 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Repository\Core\Tag;

use App\Tagging\Entity\Tag\TagEntityOutboxEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TagOutboxEvent>
 */
final class TagOutboxEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TagOutboxEventEntity::class);
    }
}
