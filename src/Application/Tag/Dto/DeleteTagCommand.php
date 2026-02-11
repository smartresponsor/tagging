<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Application\Tag\Dto;

/**
 *
 */

/**
 *
 */
final readonly class DeleteTagCommand
{
    /**
     * @param string $tenant
     * @param string $id
     */
    public function __construct(
        public string $tenant,
        public string $id,
    )
    {
    }
}
