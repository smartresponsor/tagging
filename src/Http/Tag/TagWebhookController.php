<?php
declare(strict_types=1);
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
namespace App\Http\Tag;

use App\Service\Tag\Webhook\TagWebhookRegistry;
use App\Service\Tag\Audit\TagAuditEmitter;

final class TagWebhookController
{
    public function __construct(private TagWebhookRegistry $reg, private TagAuditEmitter $audit){}

    public function subscribe($requestBody): array
    {
        $url = $requestBody['url'] ?? '';
        $secret = $requestBody['secret'] ?? null;
        if ($url === '') return ['error'=>'url_required'];
        $this->reg->add($url, $secret);
        return ['ok'=>true];
    }

    public function list(): array
    {
        return ['items'=>$this->reg->list()];
    }

    public function test(): array
    {
        // Emit a test event; emitter will fanout via registry
        $this->audit->emit('tag.created', ['id'=>'test', 'label'=>'_test_']);
        return ['ok'=>true];
    }
}
