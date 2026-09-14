<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Tagging\Entity\Tag\TagEntity;
use App\Tagging\Entity\Tag\TagRelationEntity;
use App\Tagging\Service\Core\TagGraph;
use PHPUnit\Framework\TestCase;

final class TagGraphTest extends TestCase
{
    public function testNoCycle(): void
    {
        $a = TagEntity::create('tenant-a', 'a', 'a', 'A');
        $b = TagEntity::create('tenant-a', 'b', 'b', 'B');
        $c = TagEntity::create('tenant-a', 'c', 'c', 'C');
        $adj = [
            $b->id() => [TagRelationEntity::create('relation-1', $b, $c, 'broader')],
        ];

        self::assertFalse(TagGraph::wouldCreateCycle($a->id(), $b->id(), $adj));
    }
}
