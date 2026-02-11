<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Data\Tag;

/**
 *
 */

/**
 *
 */
final readonly class FileTagAssignmentRepository implements TagAssignmentRepositoryInterface
{
    /**
     * @param string $path
     */
    public function __construct(private string $path)
    {
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        if (!file_exists($path)) file_put_contents($path, '');
    }

    /**
     * @param string $tagId
     * @param string $entityType
     * @param string $entityId
     * @return string
     */
    private function key(string $tagId, string $entityType, string $entityId): string
    {
        return $entityType . '|' . $entityId . '|' . $tagId;
    }

    /**
     * @return array
     */
    private function readAll(): array
    {
        $out = [];
        $h = @fopen($this->path, 'r');
        if ($h) {
            while (($line = fgets($h)) !== false) {
                $j = json_decode(trim($line), true);
                if (!is_array($j)) continue;
                $k = $this->key((string)$j['tagId'], (string)$j['entityType'], (string)$j['entityId']);
                $out[$k] = $j;
            }
            fclose($h);
        }
        return $out;
    }

    /**
     * @param array $map
     * @return void
     */
    private function writeAll(array $map): void
    {
        $h = fopen($this->path, 'w');
        foreach ($map as $j) {
            fwrite($h, json_encode($j, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
        }
        fclose($h);
    }

    /**
     * @param string $tagId
     * @param string $entityType
     * @param string $entityId
     * @return bool
     */
    public function assign(string $tagId, string $entityType, string $entityId): bool
    {
        $map = $this->readAll();
        $k = $this->key($tagId, $entityType, $entityId);
        if (isset($map[$k])) return false;
        $map[$k] = [
            'tagId' => $tagId,
            'entityType' => $entityType,
            'entityId' => $entityId,
            'createdAt' => gmdate('c'),
        ];
        $this->writeAll($map);
        return true;
    }

    /**
     * @param string $tagId
     * @param string $entityType
     * @param string $entityId
     * @return bool
     */
    public function unassign(string $tagId, string $entityType, string $entityId): bool
    {
        $map = $this->readAll();
        $k = $this->key($tagId, $entityType, $entityId);
        if (!isset($map[$k])) return false;
        unset($map[$k]);
        $this->writeAll($map);
        return true;
    }

    /**
     * @param string $entityType
     * @param string $entityId
     * @param int $limit
     * @param int $offset
     * @return array|\App\Data\Tag\AssignmentRecord[]
     */
    public function listByEntity(string $entityType, string $entityId, int $limit = 50, int $offset = 0): array
    {
        $map = $this->readAll();
        $out = [];
        foreach ($map as $j) {
            if ($j['entityType'] === $entityType && $j['entityId'] === $entityId) {
                $out[] = new AssignmentRecord($j['tagId'], $j['entityType'], $j['entityId'], $j['createdAt']);
            }
        }
        usort($out, fn($a, $b) => strcmp($a->createdAt, $b->createdAt));
        return array_slice($out, $offset, $limit);
    }

    /**
     * @param string $tagId
     * @return int[]
     */
    public function unassignAllForTag(string $tagId): array
    {
        $map = $this->readAll();
        $removed = 0;
        foreach (array_keys($map) as $k) {
            $parts = explode('|', $k);
            if (count($parts) === 3 && $parts[2] === $tagId) {
                unset($map[$k]);
                $removed++;
            }
        }
        $this->writeAll($map);
        return ['removed' => $removed];
    }
}
