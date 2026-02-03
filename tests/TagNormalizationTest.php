<?php
declare(strict_types=1);
namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Service\Tag\TagNormalizer;

final class TagNormalizationTest extends TestCase {
    public function testSlugify(): void {
        $this->assertSame('hello-world', TagNormalizer::slugify(' Hello  World '));
    }
}
