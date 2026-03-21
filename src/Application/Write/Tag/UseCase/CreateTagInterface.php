<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Application\Write\Tag\UseCase;

use App\Application\Write\Tag\Dto\CreateTagCommand;
use App\Application\Write\Tag\Dto\TagResult;

interface CreateTagInterface
{
    public function execute(CreateTagCommand $command): TagResult;
}
