<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Tagging\Entity\Core\Tag\TagRelation;
use App\Tagging\Service\Core\TagGraph;
use PHPUnit\Framework\TestCase;

final class TagGraphTest extends TestCase
{
    public function testNoCycle(): void
    {
        $a = 'a';
        $b = 'b';
        $c = 'c';
        $adj = [
            $b => [TagRelation::create('tenant-a', '1', $b, $c, 'broader')],
        ];
        self::assertFalse(TagGraph::wouldCreateCycle($a, $b, $adj));
    }
}
