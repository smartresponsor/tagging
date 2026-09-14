<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Application\Write\Tag\UseCase;

use App\Tagging\Application\Write\Tag\Dto\TagPatchCommand;
use App\Tagging\Application\Write\Tag\Dto\TagResult;

interface TagPatchUseCaseInterface
{
    public function execute(TagPatchCommand $command): TagResult;
}
