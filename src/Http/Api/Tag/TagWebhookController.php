<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag;

use App\Http\Api\Tag\Responder\TagWebhookResponder;
use App\Service\Core\Tag\Audit\TagAuditEmitter;
use App\Service\Core\Tag\Webhook\TagWebhookRegistry;

final readonly class TagWebhookController
{
    public function __construct(
        private TagWebhookRegistry $registry,
        private TagAuditEmitter $audit,
        private ?TagWebhookResponder $responder = null,
    ) {
    }

    /** @param array<string,mixed> $request */
    public function subscribe(array $request): array
    {
        $body = TagHttpRequest::body($request);
        $url = trim((string) ($body['url'] ?? ''));
        $secret = isset($body['secret']) && '' !== $body['secret'] ? (string) $body['secret'] : null;

        if ('' === $url) {
            return $this->responder()->bad('url_required');
        }

        try {
            $this->registry->add($url, $secret);
        } catch (\InvalidArgumentException $e) {
            return $this->responder()->bad($e->getMessage());
        }

        return $this->responder()->ok(['url' => $url], 201);
    }

    /** @param array<string,mixed> $request */
    public function list(array $request = []): array
    {
        return $this->responder()->list($this->registry->list());
    }

    /** @param array<string,mixed> $request */
    public function test(array $request = []): array
    {
        $this->audit->emit('tag.created', ['id' => 'test', 'label' => '_test_']);

        return $this->responder()->ok(['event' => 'tag.created']);
    }

    private function responder(): TagWebhookResponder
    {
        return $this->responder ?? new TagWebhookResponder();
    }
}
