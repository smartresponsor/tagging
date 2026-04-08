<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../host-minimal/cors.php';

final class CorsHeadersTest extends TestCase
{
    public function testWildcardConfigurationRemainsWildcard(): void
    {
        $headers = tag_cors_headers('https://demo.example', '*');
        self::assertSame('*', $headers['Access-Control-Allow-Origin']);
    }

    public function testConfiguredOriginIsNotSilentlyReflected(): void
    {
        $headers = tag_cors_headers('https://evil.example', 'https://admin.example');
        self::assertSame('https://admin.example', $headers['Access-Control-Allow-Origin']);
    }

    public function testMatchingOriginIsAllowedWhenPinned(): void
    {
        $headers = tag_cors_headers('https://admin.example', 'https://admin.example');
        self::assertSame('https://admin.example', $headers['Access-Control-Allow-Origin']);
    }
}
