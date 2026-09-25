<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\HandlerInterface\Write;

use App\Tagging\Command\Input\TagPatchCommand;
use App\Tagging\DTO\Write\TagResultDTO;

interface TagPatchHandlerInterface
{
    public function execute(TagPatchCommand $command): TagResultDTO;
}
