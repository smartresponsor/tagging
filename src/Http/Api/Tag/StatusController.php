<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag;

final class StatusController
{
    private ?\Closure $dbProbe;
    private string $version;
    private ?\Closure $errorSink;

    public function __construct(?callable $dbProbe = null, ?string $version = null, ?callable $errorSink = null)
    {
        $this->dbProbe = null !== $dbProbe ? \Closure::fromCallable($dbProbe) : null;
        $this->version = null !== $version && '' !== $version ? $version : RuntimeVersion::read();
        $this->errorSink = null !== $errorSink ? \Closure::fromCallable($errorSink) : null;
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
            'service' => 'tag',
            'version' => $this->version,
            'db' => $db,
        ];
    }

    private function report(string $code, \Throwable $e, array $context = []): void
    {
        if (null === $this->errorSink) {
            return;
        }

        ($this->errorSink)([
            'code' => $code,
            'message' => $e->getMessage(),
            'exception' => $e::class,
            'context' => $context,
        ]);
    }
}
