<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

final class CallableTagErrorSink implements TagErrorSink
{
    private \Closure $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback(...);
    }

    public function report(array $error): void
    {
        ($this->callback)($error);
    }
}
