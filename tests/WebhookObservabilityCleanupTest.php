<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Http\Api\Tag\Middleware\Observe;
use App\Http\Api\Tag\Responder\TagWebhookResponder;
use App\Http\Api\Tag\TagWebhookController;
use App\Service\Core\Tag\Audit\TagAuditEmitter;
use App\Service\Core\Tag\Webhook\TagWebhookRegistry;
use PHPUnit\Framework\TestCase;

final class WebhookObservabilityCleanupTest extends TestCase
{
    public function testWebhookResponderUsesNoStoreAndStableShape(): void
    {
        $response = (new TagWebhookResponder())->bad('webhook_url_invalid');

        self::assertSame(400, $response[0]);
        self::assertSame('no-store', $response[1]['Cache-Control'] ?? null);
        self::assertStringContainsString('"ok":false', $response[2]);
        self::assertStringContainsString('webhook_url_invalid', $response[2]);
    }

    public function testWebhookControllerSubscribeAndListUseStableContract(): void
    {
        $dir = sys_get_temp_dir().'/tagging-webhooks-'.bin2hex(random_bytes(4));
        $registryPath = $dir.'/registry.json';
        $auditPath = $dir.'/audit.ndjson';
        $controller = new TagWebhookController(
            new TagWebhookRegistry($registryPath),
            new TagAuditEmitter(['events_allow' => ['tag.created'], 'audit_path' => $auditPath, 'registry_path' => $registryPath]),
        );

        [$statusCreate, , $bodyCreate] = $controller->subscribe(['body' => ['url' => 'https://example.com/hook', 'secret' => 's']]);
        $created = json_decode($bodyCreate, true);
        [$statusList, , $bodyList] = $controller->list();
        $listed = json_decode($bodyList, true);

        self::assertSame(201, $statusCreate);
        self::assertTrue($created['ok'] ?? false);
        self::assertSame('https://example.com/hook', $created['url'] ?? null);
        self::assertSame(200, $statusList);
        self::assertSame(1, $listed['total'] ?? null);
        self::assertSame('https://example.com/hook', $listed['items'][0]['url'] ?? null);
    }

    public function testObserveMiddlewareWritesSlowlogForSlowRequests(): void
    {
        $dir = sys_get_temp_dir().'/tagging-observe-'.bin2hex(random_bytes(4));
        $path = $dir.'/slowlog.ndjson';
        $middleware = new Observe(['slowlog_path' => $path, 'threshold_ms' => ['read' => 0, 'write' => 0]]);

        $response = $middleware->handle(['method' => 'GET', 'path' => '/tag/search'], static function (): array {
            usleep(1500);

            return [200, ['Content-Type' => 'application/json'], '{"ok":true}'];
        });

        self::assertSame(200, $response[0]);
        self::assertFileExists($path);
        $slowlog = (string) file_get_contents($path);
        self::assertStringContainsString('/tag/search', $slowlog);
        self::assertStringContainsString('"method":"GET"', $slowlog);
    }

    public function testHostMinimalBootstrapExportsWebhookAndObserveEntries(): void
    {
        $container = require dirname(__DIR__).'/host-minimal/bootstrap.php';
        self::assertArrayHasKey('webhookController', $container);
        self::assertArrayHasKey('observeMiddleware', $container);
    }
}
