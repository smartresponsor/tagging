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
enum TagError: string
{
    case InvalidTenant = 'invalid_tenant';
    case ValidationFailed = 'validation_failed';
    case NotFound = 'not_found';
    case Conflict = 'conflict';
}
