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
final class FileTagRedirectRepository
{
    /**
     * @param string $path
     */
    public function __construct(private readonly string $path = 'report/tag/redirect.ndjson')
    {
        $dir = dirname($this->path);
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
        if (!file_exists($this->path)) file_put_contents($this->path, '');
    }

    /**
     * @param string $fromId
     * @param string $toId
     * @return void
     */
    public function put(string $fromId, string $toId): void
    {
        $line = json_encode(['fromId' => $fromId, 'toId' => $toId, 'ts' => gmdate('c')], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        file_put_contents($this->path, $line . "\n", FILE_APPEND);
    }

    /** @return array<string,string> latest map */
    private function latestMap(): array
    {
        $map = [];
        $h = @fopen($this->path, 'r');
        if ($h) {
            while (($line = fgets($h)) !== false) {
                $j = json_decode(trim($line), true);
                if (!is_array($j)) continue;
                $map[(string)($j['fromId'] ?? '')] = (string)($j['toId'] ?? '');
            }
            fclose($h);
        }
        return $map;
    }

    /** Resolve chain with maxDepth to avoid loops */
    public function resolve(string $id, int $maxDepth = 8): string
    {
        $map = $this->latestMap();
        $cur = $id;
        $i = 0;
        while (isset($map[$cur]) && $i < $maxDepth) {
            $cur = $map[$cur];
            $i++;
        }
        return $cur;
    }
}
