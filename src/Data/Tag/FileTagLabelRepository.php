<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Data\Tag;

use function dirname;

/**
 *
 */

/**
 *
 */
final readonly class FileTagLabelRepository
{
    /**
     * @param string $path
     */
    public function __construct(private string $path = 'report/tag/label.ndjson')
    {
        $dir = dirname($this->path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        if (!file_exists($this->path)) file_put_contents($this->path, '');
    }

    /** @return array<int, array{tagId:string,label:string,slug:string,usageCount:int}> */
    public function all(): array
    {
        $out = [];
        $h = @fopen($this->path, 'r');
        if ($h) {
            while (($line = fgets($h)) !== false) {
                $j = json_decode(trim($line), true);
                if (!is_array($j)) continue;
                $out[] = [
                    'tagId' => (string)($j['tagId'] ?? ''),
                    'label' => (string)($j['label'] ?? ''),
                    'slug' => (string)($j['slug'] ?? ''),
                    'usageCount' => (int)($j['usageCount'] ?? 0),
                ];
            }
            fclose($h);
        }
        return $out;
    }

    /**
     * @param string $tagId
     * @param string $label
     * @param string $slug
     * @param int $usageCount
     * @return void
     */
    public function upsert(string $tagId, string $label, string $slug, int $usageCount = 0): void
    {
        $rows = $this->all();
        $found = false;
        foreach ($rows as &$r) {
            if ($r['tagId'] === $tagId) {
                $r['label'] = $label;
                $r['slug'] = $slug;
                $r['usageCount'] = $usageCount;
                $found = true;
                break;
            }
        }
        if (!$found) $rows[] = ['tagId' => $tagId, 'label' => $label, 'slug' => $slug, 'usageCount' => $usageCount];
        $h = fopen($this->path, 'w');
        unset($r);
        foreach ($rows as $r) {
            fwrite($h, json_encode($r, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
        }
        fclose($h);
    }
}
