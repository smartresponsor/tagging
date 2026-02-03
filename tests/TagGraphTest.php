<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Service\Tag\TagGraph;
use App\Domain\Tag\TagRelation;

final class TagGraphTest extends TestCase {
    public function testNoCycle(): void {
        $a = 'a'; $b = 'b'; $c = 'c';
        $adj = [
            $b => [TagRelation::create('1',$b,$c,'broader')]
        ];
        $this->assertFalse(TagGraph::wouldCreateCycle($a, $b, $adj));
    }
}
