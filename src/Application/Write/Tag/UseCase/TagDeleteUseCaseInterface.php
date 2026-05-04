<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Application\Write\Tag\UseCase;

use App\Tagging\Application\Write\Tag\Dto\TagDeleteCommand;
use App\Tagging\Application\Write\Tag\Dto\TagResult;

interface TagDeleteUseCaseInterface
{
    public function execute(TagDeleteCommand $command): TagResult;
}
