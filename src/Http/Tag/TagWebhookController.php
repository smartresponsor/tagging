<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Tag;

use App\Service\Tag\Audit\TagAuditEmitter;
use App\Service\Tag\Webhook\TagWebhookRegistry;

/**
 *
 */

/**
 *
 */
final readonly class TagWebhookController
{
    /**
     * @param \App\Service\Tag\Webhook\TagWebhookRegistry $reg
     * @param \App\Service\Tag\Audit\TagAuditEmitter $audit
     */
    public function __construct(private TagWebhookRegistry $reg, private TagAuditEmitter $audit)
    {
    }

    /**
     * @param $requestBody
     * @return string[]|true[]
     */
    /**
     * @param $requestBody
     * @return string[]|true[]
     */
    public function subscribe($requestBody): array
    {
        $url = $requestBody['url'] ?? '';
        $secret = $requestBody['secret'] ?? null;
        if ($url === '') return ['error' => 'url_required'];
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
