<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core\Tag;

final class TagErrorSinkFactory
{
    public static function from(TagErrorSink|callable|null $sink = null): TagErrorSink
    {
        if ($sink instanceof TagErrorSink) {
            return $sink;
        }

        if (null !== $sink) {
            return new CallableTagErrorSink($sink);
        }

        return new NullTagErrorSink();
    }
}
