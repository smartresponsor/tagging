<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Http\Api\Tag\Middleware\TagMiddlewarePipeline;
use App\Http\Api\Tag\Middleware\VerifySignature;
use App\Ops\Security\NonceStore;
use App\Service\Security\HmacV2Verifier;
use PHPUnit\Framework\TestCase;

final class SecurityMiddlewareContractTest extends TestCase
{
    public function testVerifySignatureRejectsMissingHeadersWithStableJsonContract(): void
    {
        $store = new NonceStore(sys_get_temp_dir().'/tag-nonce-'.uniqid('', true), 300, 1000);
        $middleware = new VerifySignature(
            new HmacV2Verifier('secret', 120, $store),
            [
                'enforce' => true,
                'secret' => 'secret',
                'apply' => ['include' => ['/tag/**'], 'exclude' => ['/tag/_status']],
            ],
        );

        [$status, $headers, $body] = $middleware->handle(
            ['method' => 'POST', 'path' => '/tag', 'headers' => [], 'body' => '{}'],
            static fn (array $request): array => [
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['ok' => true]),
            ],
        );

        self::assertSame(401, $status);
        self::assertSame('application/json', $headers['Content-Type'] ?? null);
        self::assertSame('no-store', $headers['Cache-Control'] ?? null);
        self::assertSame('HMAC-SHA256', $headers['WWW-Authenticate'] ?? null);
        self::assertSame(
            ['ok' => false, 'code' => 'signature_missing'],
            json_decode($body, true, 512, JSON_THROW_ON_ERROR),
        );
    }

    public function testVerifySignatureSkipsExcludedMetaRoutes(): void
    {
        $store = new NonceStore(sys_get_temp_dir().'/tag-nonce-'.uniqid('', true), 300, 1000);
        $middleware = new VerifySignature(
            new HmacV2Verifier('secret', 120, $store),
            [
                'enforce' => true,
                'secret' => 'secret',
                'apply' => ['include' => ['/tag/**'], 'exclude' => ['/tag/_status']],
            ],
        );

        [$status, , $body] = $middleware->handle(
            ['method' => 'GET', 'path' => '/tag/_status', 'headers' => [], 'body' => ''],
            static fn (array $request): array => [
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['ok' => true]),
            ],
        );

        self::assertSame(200, $status);
        self::assertSame(['ok' => true], json_decode($body, true, 512, JSON_THROW_ON_ERROR));
    }

    public function testPipelineWrapsMiddlewaresAndDestinationInStableOrder(): void
    {
        $trace = new class {
            /** @var list<string> */
            public array $events = [];
        };

        $middlewareA = new class($trace) {
            public function __construct(private object $trace)
            {
            }

            public function handle(array $request, callable $next): array
            {
                $this->trace->events[] = 'a:before';
                $result = $next($request);
                $this->trace->events[] = 'a:after';

                return $result;
            }
        };

        $middlewareB = new class($trace) {
            public function __construct(private object $trace)
            {
            }

            public function handle(array $request, callable $next): array
            {
                $this->trace->events[] = 'b:before';
                $result = $next($request);
                $this->trace->events[] = 'b:after';

                return $result;
            }
        };

        $pipeline = new TagMiddlewarePipeline([$middlewareA, $middlewareB]);
        $response = $pipeline->handle(
            ['method' => 'GET', 'path' => '/tag/_status'],
            function (array $request) use ($trace): array {
                $trace->events[] = 'destination';

                return [200, ['Content-Type' => 'application/json'], json_encode(['ok' => true])];
            },
        );

        self::assertSame(
            ['a:before', 'b:before', 'destination', 'b:after', 'a:after'],
            $trace->events,
        );
        self::assertSame(200, $response[0]);
    }
}
