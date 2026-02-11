<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Tag\Audit;

use App\Service\Tag\Webhook\TagWebhookSender;

/**
 *
 */

/**
 *
 */
final readonly class TagAuditEmitter
{
    /**
     * @param array $cfg
     * @param \App\Service\Tag\Webhook\TagWebhookSender|null $sender
     */
    public function __construct(private array $cfg, private ?TagWebhookSender $sender = null)
    {
    }

    /**
     * @param string $type
     * @param array $payload
     * @return void
     */
    public function emit(string $type, array $payload): void
    {
        $allow = $this->cfg['events_allow'] ?? [];
        if (!in_array($type, $allow, true)) return;

        $line = json_encode([
            'ts' => gmdate('c'),
            'type' => $type,
            'payload' => $payload,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $path = $this->cfg['audit_path'] ?? 'report/tag/audit.ndjson';
        $dir = dirname($path);
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
        file_put_contents($path, $line . "\n", FILE_APPEND);

        $this->fanout($type, $payload);
    }

    /**
     * @param string $type
     * @param array $payload
     * @return void
     * @throws \Random\RandomException
     */
    private function fanout(string $type, array $payload): void
    {
        $regPath = $this->cfg['registry_path'] ?? 'report/webhook/registry.json';
        $registry = [];
        if (is_file($regPath)) {
            $json = file_get_contents($regPath);
            $registry = json_decode($json, true) ?: [];
        }
        foreach ($registry as $sub) {
            $url = $sub['url'] ?? null;
            if (!$url) continue;
            $secret = $sub['secret'] ?? ($this->cfg['secret_fallback'] ?? '');
            $this->sender?->enqueue($url, $secret, $type, $payload);
        }
    }
}
