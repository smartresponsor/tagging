<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag\Responder;

final class TagWebhookResponder
{
    private JsonResponder $json;

    public function __construct()
    {
        $this->json = new JsonResponder();
    }

    /** @return array{int,array<string,string>,string} */
    public function ok(array $payload = [], int $status = 200): array
    {
        return $this->json->respond($status, ['ok' => true] + $payload);
    }

    /** @return array{int,array<string,string>,string} */
    public function bad(string $code, int $status = 400, array $extra = []): array
    {
        return $this->json->reject($status, $code, $extra);
    }

    /** @param array<int,array<string,mixed>> $items
     * @return array{int,array<string,string>,string}
     */
    public function list(array $items): array
    {
        return $this->ok(['items' => $items, 'total' => count($items)]);
    }
}
