<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Service\Core\Webhook\TagWebhookRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;

final readonly class TagWebhookIndexService
{
    public function __construct(private TagWebhookRegistry $registry) {}

    public function __invoke(): JsonResponse
    {
        return new JsonResponse(['ok' => true, 'items' => $this->registry->list()]);
    }
}
