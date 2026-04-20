<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
// /tag/_metrics endpoint (plain text). Framework-agnostic stub.

namespace App\Tagging\Http\Api\Tag;

use App\Tagging\Service\Core\Tag\Metric\TagMetrics;

final class TagMetricsController
{
    public function metrics(): string
    {
        // Example default gauges/counters to exist even before traffic
        TagMetrics::inc('tag_up', 1);

        return TagMetrics::render();
    }
}
