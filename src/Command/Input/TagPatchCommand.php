<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Command\Input;

final readonly class TagPatchCommand
{
    /** @param array<string,mixed> $payload */
    public function __construct(
        public string $tenant,
        public string $id,
        public array $payload,
    ) {}
}
