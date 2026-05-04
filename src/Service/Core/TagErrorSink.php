<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core;

interface TagErrorSink
{
    /** @param array<string,mixed> $error */
    public function report(array $error): void;
}
