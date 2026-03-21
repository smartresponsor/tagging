<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Application\Write\Tag\UseCase;

use App\Application\Write\Tag\Dto\DeleteTagCommand;
use App\Application\Write\Tag\Dto\TagResult;

interface DeleteTagInterface
{
    public function execute(DeleteTagCommand $command): TagResult;
}
