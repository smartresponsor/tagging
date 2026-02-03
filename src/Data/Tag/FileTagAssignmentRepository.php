<?php
declare(strict_types=1);
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
namespace App\Data\Tag;

final class FileTagAssignmentRepository implements TagAssignmentRepositoryInterface
{
    public function __construct(private string $path)
    {
        $dir = dirname($path);
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
        if (!file_exists($path)) file_put_contents($path, '');
    }

    private function key(string $tagId, string $entityType, string $entityId): string
    {
        return $entityType . '|' . $entityId . '|' . $tagId;
    }

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

    private function writeAll(array $map): void
    {
        $h = fopen($this->path, 'w');
        foreach ($map as $j) {
            fwrite($h, json_encode($j, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) . "\n");
        }
        fclose($h);
    }

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

    public function unassign(string $tagId, string $entityType, string $entityId): bool
    {
        $map = $this->readAll();
        $k = $this->key($tagId, $entityType, $entityId);
        if (!isset($map[$k])) return false;
        unset($map[$k]);
        $this->writeAll($map);
        return true;
    }

    public function listByEntity(string $entityType, string $entityId, int $limit=50, int $offset=0): array
    {
        $map = $this->readAll();
        $out = [];
        foreach ($map as $j) {
            if ($j['entityType'] === $entityType && $j['entityId'] === $entityId) {
                $out[] = new AssignmentRecord($j['tagId'], $j['entityType'], $j['entityId'], $j['createdAt']);
            }
        }
        usort($out, fn($a,$b) => strcmp($a->createdAt, $b->createdAt));
        return array_slice($out, $offset, $limit);
    }

    public function unassignAllForTag(string $tagId): array
    {
        $map = $this->readAll(); $removed = 0;
        foreach (array_keys($map) as $k) {
            $parts = explode('|', $k);
            if (count($parts) === 3 && $parts[2] === $tagId) { unset($map[$k]); $removed++; }
        }
        $this->writeAll($map);
        return ['removed' => $removed];
    }
}
