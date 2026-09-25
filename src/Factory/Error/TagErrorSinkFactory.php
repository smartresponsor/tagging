<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Factory\Error;

use App\Tagging\Service\Core\TagCallableErrorSink;
use App\Tagging\Service\Core\TagErrorSink;
use App\Tagging\Service\Core\TagNullErrorSink;

final class TagErrorSinkFactory
{
    public static function from(TagErrorSink|callable|null $sink = null): TagErrorSink
    {
        if ($sink instanceof TagErrorSink) {
            return $sink;
        }

        if (null !== $sink) {
            return new TagCallableErrorSink($sink);
        }

        return new TagNullErrorSink();
    }
}
