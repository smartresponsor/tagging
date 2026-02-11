<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Service\Tag\TagNormalizer;
use PHPUnit\Framework\TestCase;

/**
 *
 */

/**
 *
 */
final class TagNormalizationTest extends TestCase
{
    /**
     * @return void
     */
    public function testSlugify(): void
    {
        static::assertSame('hello-world', TagNormalizer::slugify(' Hello  World '));
    }
}
