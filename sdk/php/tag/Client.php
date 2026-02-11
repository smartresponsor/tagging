<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace SR\SDK\Tag;

use RuntimeException;

/**
 * Smartresponsor Tag SDK (PHP, E11)
 */
final readonly class Client
{
    /**
     * @param string $baseUrl
     * @param array $headers
     */
    public function __construct(private string $baseUrl, private array $headers = [])
    {
    }

    /**
     * @param string $path
     * @param string $method
     * @param array|null $body
     * @return array
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
                $this->headers
            )
        );
        $payload = null;
        if ($body !== null) {
            $payload = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($payload === false) {
                throw new RuntimeException('Unable to encode request body.');
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
                throw new RuntimeException('HTTP request failed: ' . curl_error($ch));
            }
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
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
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON response.');
        }
        return $decoded ?? [];
    }

    public function list(string $q = '', int $limit = 20, int $offset = 0): array
    {
        return $this->req('/tag?query=' . urlencode($q) . '&limit=' . $limit . '&offset=' . $offset);
    }

    /**
     * @param string $label
     * @param string|null $slug
     * @return array
     */
    public function create(string $label, ?string $slug = null): array
    {
        return $this->req('/tag', 'POST', ['label' => $label, 'slug' => $slug]);
    }

    /**
     * @param string $id
     * @return array
     */
    public function remove(string $id): array
    {
        return $this->req('/tag/' . $id, 'DELETE');
    }

    /**
     * @param string $tagId
     * @param string $type
     * @param string $id
     * @return array
     */
    public function assign(string $tagId, string $type, string $id): array
    {
        return $this->req('/tag/assign', 'POST', ['tagId' => $tagId, 'assignedType' => $type, 'assignedId' => $id]);
    }

    /**
     * @param string $type
     * @param int $limit
     * @return array
     */
    public function facet(string $type, int $limit = 50): array
    {
        return $this->req('/tag/facet?type=' . urlencode($type) . '&limit=' . $limit);
    }

    /**
     * @param int $limit
     * @return array
     */
    public function cloud(int $limit = 100): array
    {
        return $this->req('/tag/cloud?limit=' . $limit);
    }

    /**
     * @param string $tagId
     * @param string $locale
     * @param string $label
     * @return array
     */
    public function putLabel(string $tagId, string $locale, string $label): array
    {
        return $this->req('/tag/' . $tagId . '/label', 'POST', ['locale' => $locale, 'label' => $label]);
    }

    /**
     * @param string $tagId
     * @return array
     */
    public function listLabels(string $tagId): array
    {
        return $this->req('/tag/' . $tagId . '/labels');
    }

    /**
     * @param string $tagId
     * @param string $key
     * @param string $value
     * @return array
     */
    public function classify(string $tagId, string $key, string $value): array
    {
        return $this->req('/tag/' . $tagId . '/classify', 'POST', ['key' => $key, 'value' => $value]);
    }

    /**
     * @param string $tagId
     * @return array
     */
    public function replay(string $tagId): array
    {
        return $this->req('/tag/' . $tagId . '/replay', 'POST');
    }

    /**
     * @param array $body
     * @return array
     */
    public function putPolicy(array $body): array
    {
        return $this->req('/tag/policy', 'PUT', $body);
    }

    /**
     * @return array
     */
    public function auditPolicy(): array
    {
        return $this->req('/tag/policy/report');
    }

    /**
     * @param int $perMinute
     * @param int $maxTagsPerEntity
     * @return array
     */
    public function putQuota(int $perMinute, int $maxTagsPerEntity): array
    {
        return $this->req('/tag/quota', 'PUT', [
            'per_minute' => $perMinute,
            'max_tags_per_entity' => $maxTagsPerEntity,
        ]);
    }

    /**
     * @param string $fromId
     * @param string $toTagId
     * @param bool $moveAssignments
     * @param bool $copySynonyms
     * @return array
     */
    public function merge(
        string $fromId,
        string $toTagId,
        bool   $moveAssignments = true,
        bool   $copySynonyms = true
    ): array
    {
        return $this->req('/tag/' . $fromId . '/merge', 'POST', [
            'toTagId' => $toTagId,
            'moveAssignments' => $moveAssignments,
            'copySynonyms' => $copySynonyms,
        ]);
    }

    /**
     * @param string $id
     * @param array $newTags
     * @return array
     */
    public function split(string $id, array $newTags): array
    {
        return $this->req('/tag/' . $id . '/split', 'POST', ['newTags' => $newTags]);
    }

    /**
     * @param array $items
     * @return array
     */
    public function bulkImport(array $items): array
    {
        return $this->req('/tag/bulk/import', 'POST', ['items' => $items]);
    }

    /**
     * @param string $jobId
     * @return array
     */
    public function bulkJobStatus(string $jobId): array
    {
        return $this->req('/tag/bulk/' . $jobId);
    }

    /**
     * @param string $fromId
     * @return array
     */
    public function resolveRedirect(string $fromId): array
    {
        return $this->req('/tag/redirect/' . $fromId);
    }
}
