<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core;

final class TagNullErrorSink implements TagErrorSink
{
    public function report(array $error): void {}
}
