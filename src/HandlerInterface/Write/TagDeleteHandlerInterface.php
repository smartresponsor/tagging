<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\HandlerInterface\Write;

use App\Tagging\Command\Input\TagDeleteCommand;
use App\Tagging\DTO\Write\TagResultDTO;

interface TagDeleteHandlerInterface
{
    public function execute(TagDeleteCommand $command): TagResultDTO;
}
