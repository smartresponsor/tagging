<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\HandlerInterface\Write;

use App\Tagging\Command\Input\TagCreateCommand;
use App\Tagging\DTO\Write\TagResultDTO;

interface TagCreateHandlerInterface
{
    public function execute(TagCreateCommand $command): TagResultDTO;
}
