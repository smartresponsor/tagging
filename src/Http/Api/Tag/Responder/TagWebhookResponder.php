<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag\Responder;

final class TagWebhookResponder
{
    /** @return array{int,array<string,string>,string} */
    public function ok(array $payload = [], int $status = 200): array
    {
        return $this->json($status, ['ok' => true] + $payload);
    }

    /** @return array{int,array<string,string>,string} */
    public function bad(string $code, int $status = 400, array $extra = []): array
    {
        return $this->json($status, ['ok' => false, 'code' => $code] + $extra);
    }

    /** @param array<int,array<string,mixed>> $items
     * @return array{int,array<string,string>,string}
     */
    public function list(array $items): array
    {
        return $this->ok(['items' => $items, 'total' => count($items)]);
    }

    /** @return array{int,array<string,string>,string} */
    private function json(int $status, array $payload): array
    {
        return [
            $status,
            [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-store',
            ],
            json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{"ok":false,"code":"encode_error"}',
        ];
    }
}
