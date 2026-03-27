<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Application\Write\Tag\Dto;

final readonly class DeleteTagCommand
{
    public function __construct(
        public string $tenant,
        public string $id,
    ) {}
}
