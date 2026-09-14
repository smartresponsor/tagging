<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Service\Core\Metric\TagMetrics;
use Symfony\Component\HttpFoundation\Response;

final readonly class TagMetricsService
{
    public function __invoke(): Response
    {
        TagMetrics::inc('tag_up');

        return new Response(
            TagMetrics::render(),
            200,
            ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8'],
        );
    }
}
