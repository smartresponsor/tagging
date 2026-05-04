<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Tagging\Service\Core\TagCallableErrorSink;
use App\Tagging\Service\Core\TagNullErrorSink;
use App\Tagging\Service\Core\TagErrorSink;
use App\Tagging\Service\Core\TagErrorSinkFactory;
use PHPUnit\Framework\TestCase;

final class TagErrorSinkFactoryTest extends TestCase
{
    public function testFactoryKeepsProvidedSinkInstance(): void
    {
        $sink = new class implements TagErrorSink {
            public array $errors = [];

            public function report(array $error): void
            {
                $this->errors[] = $error;
            }
        };

        self::assertSame($sink, TagErrorSinkFactory::from($sink));
    }

    public function testFactoryWrapsCallableAndNullFallback(): void
    {
        $errors = [];
        $callableSink = TagErrorSinkFactory::from(static function (array $error) use (&$errors): void {
            $errors[] = $error;
        });
        $nullSink = TagErrorSinkFactory::from();

        self::assertInstanceOf(TagCallableErrorSink::class, $callableSink);
        self::assertInstanceOf(TagNullErrorSink::class, $nullSink);

        $callableSink->report(['code' => 'demo']);
        $nullSink->report(['code' => 'ignored']);

        self::assertSame([['code' => 'demo']], $errors);
    }
}
