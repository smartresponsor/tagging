<?php
declare(strict_types=1);
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
namespace App\Http\Tag;

use App\Ops\Metrics\TagMetrics;

final class MetricsController
{
    /** @return array{int,array<string,string>,string} */
    public function metrics(): array
    {
        $body = TagMetrics::exporter()->renderText();
        return [200, ['Content-Type'=>'text/plain; version=0.0.4'], $body];
    }
}
