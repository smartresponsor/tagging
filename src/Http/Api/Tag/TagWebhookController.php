<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag;

use App\Service\Audit\Tag\TagAuditEmitter;
use App\Service\Webhook\Tag\TagWebhookRegistry;

final readonly class TagWebhookController
{
    public function __construct(private TagWebhookRegistry $reg, private TagAuditEmitter $audit)
    {
    }

    /**
     * @return string[]|true[]
     */
    /**
     * @return string[]|true[]
     */
    public function subscribe($requestBody): array
    {
        $url = $requestBody['url'] ?? '';
        $secret = $requestBody['secret'] ?? null;
        if ('' === $url) {
            return ['error' => 'url_required'];
        }
        $this->reg->add($url, $secret);

        return ['ok' => true];
    }

    public function list(): array
    {
        return ['items' => $this->reg->list()];
    }

    /**
     * @return true[]
     */
    public function test(): array
    {
        // Emit a test event; emitter will fanout via registry
        $this->audit->emit('tag.created', ['id' => 'test', 'label' => '_test_']);

        return ['ok' => true];
    }
}
