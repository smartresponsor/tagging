<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Tagging\Http\Api\Tag\CorsHeaders;
use PHPUnit\Framework\TestCase;

final class CorsHeadersTest extends TestCase
{
    public function testWildcardConfigurationRemainsWildcard(): void
    {
        $headers = CorsHeaders::forOrigin('https://demo.example', '*');
        self::assertSame('*', $headers['Access-Control-Allow-Origin']);
    }

    public function testConfiguredOriginIsNotSilentlyReflected(): void
    {
        $headers = CorsHeaders::forOrigin('https://evil.example', 'https://admin.example');
        self::assertSame('https://admin.example', $headers['Access-Control-Allow-Origin']);
    }

    public function testMatchingOriginIsAllowedWhenPinned(): void
    {
        $headers = CorsHeaders::forOrigin('https://admin.example', 'https://admin.example');
        self::assertSame('https://admin.example', $headers['Access-Control-Allow-Origin']);
    }
}
