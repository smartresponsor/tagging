<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Service\Core\Tag\TagNormalizer;
use PHPUnit\Framework\TestCase;

final class TagNormalizationTest extends TestCase
{
    public function testSlugify(): void
    {
        self::assertSame('hello-world', TagNormalizer::slugify(' Hello  World '));
    }
}
