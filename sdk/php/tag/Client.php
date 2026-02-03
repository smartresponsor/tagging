<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace SR\SDK\Tag;

/**
 * Smartresponsor Tag SDK (PHP, E11)
 */
final class Client
{
    public function __construct(private readonly string $baseUrl, private array $headers = [])
    {
    }

    private function req(string $path, string $method = 'GET', ?array $body = null): array
    {
        $ch = curl_init(rtrim($this->baseUrl, '/') . $path);
        if ($ch === false) {
            throw new \RuntimeException('Unable to initialize curl.');
        }
        $hdrs = array_merge(
            ['Content-Type: application/json'],
            array_map(
                static fn(string $k, string $v): string => $k . ': ' . $v,
                array_keys($this->headers),
                $this->headers
            )
        );
        $payload = null;
        if ($body !== null) {
            $payload = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($payload === false) {
                throw new \RuntimeException('Unable to encode request body.');
            }
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
                throw new \RuntimeException('HTTP request failed: ' . curl_error($ch));
            }
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        } finally {
            curl_close($ch);
        }

        if ($code >= 400) {
            throw new \RuntimeException('HTTP ' . $code . ' ' . $res);
        }
        if ($res === '') {
            return [];
        }
        $decoded = json_decode($res, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON response.');
        }
        return $decoded ?? [];
    }

    public function list(string $q = '', int $limit = 20, int $offset = 0): array
    {
        return $this->req('/tag?query=' . urlencode($q) . '&limit=' . $limit . '&offset=' . $offset);
    }

    public function create(string $label, ?string $slug = null): array
    {
        return $this->req('/tag', 'POST', ['label' => $label, 'slug' => $slug]);
    }

    public function remove(string $id): array
    {
        return $this->req('/tag/' . $id, 'DELETE');
    }

    public function assign(string $tagId, string $type, string $id): array
    {
        return $this->req('/tag/assign', 'POST', ['tagId' => $tagId, 'assignedType' => $type, 'assignedId' => $id]);
    }

    public function facet(string $type, int $limit = 50): array
    {
        return $this->req('/tag/facet?type=' . urlencode($type) . '&limit=' . $limit);
    }

    public function cloud(int $limit = 100): array
    {
        return $this->req('/tag/cloud?limit=' . $limit);
    }

    public function putLabel(string $tagId, string $locale, string $label): array
    {
        return $this->req('/tag/' . $tagId . '/label', 'POST', ['locale' => $locale, 'label' => $label]);
    }

    public function listLabels(string $tagId): array
    {
        return $this->req('/tag/' . $tagId . '/labels');
    }

    public function classify(string $tagId, string $key, string $value): array
    {
        return $this->req('/tag/' . $tagId . '/classify', 'POST', ['key' => $key, 'value' => $value]);
    }

    public function replay(string $tagId): array
    {
        return $this->req('/tag/' . $tagId . '/replay', 'POST');
    }

    public function putPolicy(array $body): array
    {
        return $this->req('/tag/policy', 'PUT', $body);
    }

    public function auditPolicy(): array
    {
        return $this->req('/tag/policy/report');
    }

    public function putQuota(int $perMinute, int $maxTagsPerEntity): array
    {
        return $this->req('/tag/quota', 'PUT', [
            'per_minute' => $perMinute,
            'max_tags_per_entity' => $maxTagsPerEntity,
        ]);
    }

    public function merge(
        string $fromId,
        string $toTagId,
        bool $moveAssignments = true,
        bool $copySynonyms = true
    ): array {
        return $this->req('/tag/' . $fromId . '/merge', 'POST', [
            'toTagId' => $toTagId,
            'moveAssignments' => $moveAssignments,
            'copySynonyms' => $copySynonyms,
        ]);
    }

    public function split(string $id, array $newTags): array
    {
        return $this->req('/tag/' . $id . '/split', 'POST', ['newTags' => $newTags]);
    }

    public function bulkImport(array $items): array
    {
        return $this->req('/tag/bulk/import', 'POST', ['items' => $items]);
    }

    public function bulkJobStatus(string $jobId): array
    {
        return $this->req('/tag/bulk/' . $jobId);
    }

    public function resolveRedirect(string $fromId): array
    {
        return $this->req('/tag/redirect/' . $fromId);
    }
}
