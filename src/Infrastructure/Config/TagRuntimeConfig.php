<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure\Config;

final readonly class TagRuntimeConfig
{
    /**
     * @param array<string,mixed> $runtime
     * @param list<string>        $entityTypes
     * @param array<string,mixed> $webhook
     * @param array<string,mixed> $observability
     * @param array<string,mixed> $security
     */
    public function __construct(
        public array $runtime,
        public string $runtimeVersion,
        public string $dbDsn,
        public string $dbUser,
        public string $dbPass,
        public string $defaultTenant,
        public array $entityTypes,
        public array $webhook,
        public array $observability,
        public array $security,
    ) {}
}
