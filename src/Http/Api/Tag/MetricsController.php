<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Http\Api\Tag;

use App\Tagging\Ops\Metrics\TagMetrics;

final class MetricsController
{
    /** @return array{int,array<string,string>,string} */
    public function metrics(): array
    {
        $body = TagMetrics::exporter()->renderText();

        return [200, ['Content-Type' => 'text/plain; version=0.0.4'], $body];
    }
}
