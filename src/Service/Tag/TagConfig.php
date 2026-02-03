<?php
declare(strict_types=1);
namespace App\Service\Tag;

/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
final class TagConfig {
    public function __construct(
        public readonly int $maxTagLength = 255,
        public readonly int $defaultMaxTagsPerEntity = 250
    ){}
}
