<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Domain\Tag\TagRelation;
use App\Service\Tag\TagGraph;
use PHPUnit\Framework\TestCase;

/**
 *
 */

/**
 *
 */
final class TagGraphTest extends TestCase
{
    /**
     * @return void
     */
    public function testNoCycle(): void
    {
        $a = 'a';
        $b = 'b';
        $c = 'c';
        $adj = [
            $b => [TagRelation::create('1', $b, $c, 'broader')],
        ];
        TagGraphTest::assertFalse(TagGraph::wouldCreateCycle($a, $b, $adj));
    }
}
