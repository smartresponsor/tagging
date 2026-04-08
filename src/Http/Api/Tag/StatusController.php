<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag;

use App\Service\Core\Tag\TagErrorSink;
use App\Service\Core\Tag\TagErrorSinkFactory;

final class StatusController
{
    private ?\Closure $dbProbe;
    private string $version;
    private TagErrorSink $errorSink;

    /** @var array<string,mixed> */
    private array $runtime;

    /**
     * @param array<string,mixed> $runtime
     */
    public function __construct(
        ?callable $dbProbe = null,
        ?string $version = null,
        TagErrorSink|callable|null $errorSink = null,
        array $runtime = [],
    ) {
        $this->dbProbe = null !== $dbProbe ? \Closure::fromCallable($dbProbe) : null;
        $this->version = null !== $version && '' !== $version ? $version : RuntimeVersion::read();
        $this->errorSink = TagErrorSinkFactory::from($errorSink);
        $this->runtime = $runtime;
    }

    /** @return array<string,mixed> */
    public function status(): array
    {
        $db = ['available' => null !== $this->dbProbe, 'ok' => false];
        if (null !== $this->dbProbe) {
            try {
                $db['ok'] = (bool) ($this->dbProbe)();
            } catch (\Throwable $e) {
                $db['ok'] = false;
                $db['error'] = 'db_unavailable';
                $this->report('status.db_probe_failed', $e, ['surface' => 'status']);
            }
        }

        return [
            'ok' => true,
            'ts' => gmdate('c'),
            'service' => $this->runtimeString('service', 'tag'),
            'version' => $this->version,
            'runtime' => $this->runtimeName(),
            'surface' => [
                'status' => $this->routeValue('status', '/tag/_status'),
                'discovery' => $this->routeValue('discovery', '/tag/_surface'),
            ],
            'db' => $db,
        ];
    }

    private function runtimeName(): string
    {
        return $this->runtimeString('runtime', 'host-minimal');
    }

    private function routeValue(string $key, string $fallback): string
    {
        $routes = $this->runtime['route'] ?? null;
        if (!is_array($routes)) {
            return $fallback;
        }

        $route = $routes[$key] ?? null;

        return is_string($route) && '' !== $route ? $route : $fallback;
    }

    private function runtimeString(string $key, string $fallback): string
    {
        $value = $this->runtime[$key] ?? null;

        return is_string($value) && '' !== $value ? $value : $fallback;
    }

    private function report(string $code, \Throwable $e, array $context = []): void
    {
        $this->errorSink->report([
            'code' => $code,
            'message' => $e->getMessage(),
            'exception' => $e::class,
            'context' => $context,
        ]);
    }
}
