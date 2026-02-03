<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);


namespace App\Data\Tag;

use App\DataInterface\Tag\TagAssignmentRepositoryInterface as TagAssignmentRepositoryContract;

/**
 * Backward-compatible interface kept in Data layer.
 * Canonical contract lives in DataInterface layer.
 */
interface TagAssignmentRepositoryInterface extends TagAssignmentRepositoryContract
{
}
