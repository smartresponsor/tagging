<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Application\Write\Tag\Dto;

final readonly class TagCreateCommand
{
    /** @param array<string,mixed> $payload */
    public function __construct(
        public string $tenant,
        public array $payload,
    ) {}
}
