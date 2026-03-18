<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag;

final class StatusController
{
    private ?\Closure $dbProbe;
    private string $version;

    public function __construct(?callable $dbProbe = null, ?string $version = null)
    {
        $this->dbProbe = null !== $dbProbe ? \Closure::fromCallable($dbProbe) : null;
        $this->version = null !== $version && '' !== $version ? $version : RuntimeVersion::read();
    }

    /** @return array<string,mixed> */
    public function status(): array
    {
        $db = ['available' => null !== $this->dbProbe, 'ok' => false];
        if (null !== $this->dbProbe) {
            try {
                $db['ok'] = (bool) ($this->dbProbe)();
            } catch (\Throwable) {
                $db['ok'] = false;
                $db['error'] = 'db_unavailable';
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
}
