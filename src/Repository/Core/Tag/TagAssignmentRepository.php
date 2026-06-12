<?php

// Copyright (c) 2026 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Repository\Core\Tag;

use App\Tagging\Entity\Tag\TagAssignmentEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TagAssignmentEntity>
 */
final class TagAssignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TagAssignmentEntity::class);
    }
}
