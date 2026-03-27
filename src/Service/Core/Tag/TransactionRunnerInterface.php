<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

interface TransactionRunnerInterface
{
    /**
     * @template T
     *
     * @param callable():T $callback
     *
     * @return T
     */
    public function run(callable $callback): mixed;
}
