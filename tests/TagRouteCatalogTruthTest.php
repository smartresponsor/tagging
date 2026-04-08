<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagRouteCatalogTruthTest extends TestCase
{
    public function testPublicSurfaceIsProjectedFromCanonicalRouteCatalog(): void
    {
        $catalog = require __DIR__.'/../config/tag_route_catalog.php';
        $surface = require __DIR__.'/../config/tag_public_surface.php';

        self::assertIsArray($catalog);
        self::assertIsArray($surface);
        self::assertSame('p118-route-catalog-truth', $catalog['version'] ?? null);
        self::assertSame('p118-route-catalog-truth', $surface['version'] ?? null);
        self::assertSame('POST /tag/assignments/bulk', $surface['route']['assignments_bulk'] ?? null);
        self::assertSame('GET /tag/search', $surface['route']['search'] ?? null);
        self::assertArrayNotHasKey('webhooks_list', $surface['route']);
    }

    public function testHostMinimalRouterDispatchesUsingCanonicalRouteCatalog(): void
    {
        $routeFactory = require __DIR__.'/../host-minimal/route.php';
        $calls = new \ArrayObject();
        $container = [
            'runtime' => static fn (): array => ['version' => 'route-catalog-test'],
            'tagController' => static fn (): object => new RouteCatalogSpyController($calls, 'tag'),
            'assignController' => static fn (): object => new RouteCatalogSpyController($calls, 'assign'),
            'assignmentReadController' => static fn (): object => new RouteCatalogSpyController($calls, 'assignment-read'),
            'searchController' => static fn (): object => new RouteCatalogSpyController($calls, 'search'),
            'suggestController' => static fn (): object => new RouteCatalogSpyController($calls, 'suggest'),
            'webhookController' => static fn (): object => new RouteCatalogSpyController($calls, 'webhook'),
            'statusController' => static fn (): object => new RouteCatalogStatusSpyController('status'),
            'surfaceController' => static fn (): object => new RouteCatalogStatusSpyController('surface'),
        ];

        $dispatch = $routeFactory($container);

        [$statusCode, $statusHeaders, $statusBody] = $dispatch('GET', '/tag/_status', []);
        $statusPayload = json_decode($statusBody, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(200, $statusCode);
        self::assertSame('route-catalog-test', $statusHeaders['X-Tag-Version']);
        self::assertSame('status', $statusPayload['marker']);

        $id = str_repeat('A', 26);
        [$readCode, , $readBody] = $dispatch('GET', '/tag/'.$id, ['headers' => ['X-Tenant-Id' => 'demo']]);
        $readPayload = json_decode($readBody, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(200, $readCode);
        self::assertSame('get', $readPayload['action']);
        self::assertSame($id, $readPayload['id']);

        [$bulkCode, , $bulkBody] = $dispatch('POST', '/tag/assignments/bulk', ['body' => ['operations' => []]]);
        $bulkPayload = json_decode($bulkBody, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(200, $bulkCode);
        self::assertSame('bulk', $bulkPayload['action']);
        self::assertSame('assign', $bulkPayload['controller']);
    }
}

final readonly class RouteCatalogSpyController
{
    public function __construct(private \ArrayObject $calls, private string $name)
    {
    }

    public function create(array $request): array
    {
        return $this->tuple('create');
    }

    public function get(array $request, string $id): array
    {
        return $this->tuple('get', ['id' => $id]);
    }

    public function patch(array $request, string $id): array
    {
        return $this->tuple('patch', ['id' => $id]);
    }

    public function delete(array $request, string $id): array
    {
        return $this->tuple('delete', ['id' => $id]);
    }

    public function assign(array $request, string $id): array
    {
        return $this->tuple('assign', ['id' => $id]);
    }

    public function unassign(array $request, string $id): array
    {
        return $this->tuple('unassign', ['id' => $id]);
    }

    public function bulk(array $request): array
    {
        return $this->tuple('bulk');
    }

    public function assignBulkToEntity(array $request): array
    {
        return $this->tuple('assignBulkToEntity');
    }

    public function listByEntity(array $request): array
    {
        return $this->tuple('listByEntity');
    }

    public function subscribe(array $request): array
    {
        return $this->tuple('subscribe');
    }

    public function list(array $request = []): array
    {
        return $this->tuple('list');
    }

    public function test(array $request = []): array
    {
        return $this->tuple('test');
    }

    private function tuple(string $action, array $extra = []): array
    {
        $this->calls->append([$this->name, $action]);

        return [
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['controller' => $this->name, 'action' => $action] + $extra, JSON_THROW_ON_ERROR),
        ];
    }
}

final readonly class RouteCatalogStatusSpyController
{
    public function __construct(private string $marker)
    {
    }

    public function status(): array
    {
        return ['ok' => true, 'marker' => $this->marker];
    }

    public function surface(): array
    {
        return ['ok' => true, 'marker' => $this->marker];
    }
}
