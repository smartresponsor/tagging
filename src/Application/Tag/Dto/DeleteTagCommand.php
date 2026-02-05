<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Application\Tag\Dto;

final class DeleteTagCommand
{
    public function __construct(
        public readonly string $tenant,
        public readonly string $id,
    ) {
    }
}
