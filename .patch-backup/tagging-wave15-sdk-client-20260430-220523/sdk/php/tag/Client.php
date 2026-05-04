<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace SR\SDK\Tag;

use RuntimeException;

final readonly class Client
{
    /** @param array<string,string> $headers */
    public function __construct(private string $baseUrl, private array $headers = [])
    {
    }

    /** @param array<string,mixed>|null $body
     *  @return array<string,mixed>
     */
    private function req(string $path, string $method = 'GET', ?array $body = null): array
    {
        $ch = curl_init(rtrim($this->baseUrl, '/') . $path);
        if ($ch === false) {
            throw new RuntimeException('Unable to initialize curl.');
        }

        $hdrs = array_merge(
            ['Content-Type: application/json'],
            array_map(
                static fn(string $k, string $v): string => $k . ': ' . $v,
                array_keys($this->headers),
                $this->headers,
            ),
        );
        $payload = $body !== null ? json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
        if ($body !== null && $payload === false) {
            throw new RuntimeException('Unable to encode request body.');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $hdrs,
        ]);
        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }

        try {
            $res = curl_exec($ch);
            if ($res === false) {
                throw new RuntimeException('HTTP request failed: ' . curl_error($ch));
            }
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        } finally {
            curl_close($ch);
        }

        if ($code >= 400) {
            throw new RuntimeException('HTTP ' . $code . ' ' . $res);
        }
        if ($res === '') {
            return [];
        }
        $decoded = json_decode($res, true);
        if (!is_array($decoded) && $res !== 'null') {
            throw new RuntimeException('Invalid JSON response.');
        }

        return is_array($decoded) ? $decoded : [];
    }

    /** @return array<string,mixed> */
    public function status(): array
    {
        return $this->req('/tag/_status');
    }

    /** @return array<string,mixed> */
    public function surface(): array
    {
        return $this->req('/tag/_surface');
    }

    /** @param array<string,mixed> $body
     *  @return array<string,mixed>
     */
    public function create(array $body): array
    {
        return $this->req('/tag', 'POST', $body);
    }

    /** @return array<string,mixed> */
    public function get(string $id): array
    {
        return $this->req('/tag/' . rawurlencode($id));
    }

    /** @param array<string,mixed> $body
     *  @return array<string,mixed>
     */
    public function patch(string $id, array $body): array
    {
        return $this->req('/tag/' . rawurlencode($id), 'PATCH', $body);
    }

    /** @return array<string,mixed> */
    public function delete(string $id): array
    {
        return $this->req('/tag/' . rawurlencode($id), 'DELETE');
    }

    /** @param array<string,mixed> $body
     *  @return array<string,mixed>
     */
    public function assign(string $id, array $body): array
    {
        return $this->req('/tag/' . rawurlencode($id) . '/assign', 'POST', $body);
    }

    /** @param array<string,mixed> $body
     *  @return array<string,mixed>
     */
    public function unassign(string $id, array $body): array
    {
        return $this->req('/tag/' . rawurlencode($id) . '/unassign', 'POST', $body);
    }

    /** @return array<string,mixed> */
    public function assignments(string $entityType, string $entityId): array
    {
        return $this->req(
            '/tag/assignments?entityType=' . rawurlencode($entityType)
            . '&entityId=' . rawurlencode($entityId),
        );
    }

    /** @param array<string,mixed> $body
     *  @return array<string,mixed>
     */
    public function bulkAssignments(array $body): array
    {
        return $this->req('/tag/assignments/bulk', 'POST', $body);
    }

    /** @param array<string,mixed> $body
     *  @return array<string,mixed>
     */
    public function assignBulkToEntity(array $body): array
    {
        return $this->req('/tag/assignments/bulk-to-entity', 'POST', $body);
    }

    /** @return array<string,mixed> */
    public function search(string $q, int $pageSize = 20, ?string $pageToken = null): array
    {
        $path = '/tag/search?q=' . rawurlencode($q) . '&pageSize=' . max(1, min(100, $pageSize));
        if ($pageToken !== null && $pageToken !== '') {
            $path .= '&pageToken=' . rawurlencode($pageToken);
        }

        return $this->req($path);
    }

    /** @return array<string,mixed> */
    public function suggest(string $q, int $limit = 10): array
    {
        return $this->req('/tag/suggest?q=' . rawurlencode($q) . '&limit=' . max(1, min(50, $limit)));
    }
}
