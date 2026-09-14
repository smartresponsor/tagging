<?php

// Copyright (c) 2026 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Repository\Core\Tag;

use App\Tagging\Entity\Tag\TagAssignmentEntity;
use Doctrine\ORM\EntityRepository;

/**
 * @extends EntityRepository<TagAssignmentEntity>
 */
final class TagAssignmentRepository extends EntityRepository {}
