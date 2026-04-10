<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

final class NullTagErrorSink implements TagErrorSink
{
    public function report(array $error): void {}
}
