<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Http\Api\Tag\Responder;

final class JsonResponder
{
    private const int JSON_FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function respond(int $status, array $payload, array $headers = [], bool $noStore = true): array
    {
        return [$status, $this->headers($headers, $noStore), $this->encode($payload)];
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function reject(
        int $status,
        string $code,
        array $payload = [],
        array $headers = [],
        bool $noStore = true,
    ): array {
        return $this->respond($status, ['ok' => false, 'code' => $code] + $payload, $headers, $noStore);
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function empty(int $status = 204, array $headers = [], bool $noStore = true): array
    {
        return [$status, $this->headers($headers, $noStore), ''];
    }

    /** @return array<string,string> */
    private function headers(array $headers, bool $noStore): array
    {
        $defaultHeaders = ['Content-Type' => 'application/json'];
        if ($noStore) {
            $defaultHeaders['Cache-Control'] = 'no-store';
        }

        return $headers + $defaultHeaders;
    }

    private function encode(array $payload): string
    {
        return json_encode($payload, self::JSON_FLAGS) ?: '{"ok":false,"code":"encode_error"}';
    }
}
