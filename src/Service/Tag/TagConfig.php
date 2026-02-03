<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Service\Tag;

final class TagConfig {
    public function __construct(
        public readonly int $maxTagLength = 255,
        public readonly int $defaultMaxTagsPerEntity = 250
    ){}
}
