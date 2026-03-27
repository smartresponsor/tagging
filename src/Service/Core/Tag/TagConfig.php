<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

final readonly class TagConfig
{
    public function __construct(
        public int $maxTagLength = 255,
        public int $defaultMaxTagsPerEntity = 250,
    ) {}
}
