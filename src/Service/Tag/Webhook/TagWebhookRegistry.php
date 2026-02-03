<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Service\Tag\Webhook;

final class TagWebhookRegistry
{
    public function __construct(private string $path){}

    /** @return array<int, array{url:string,secret?:string}> */
    public function list(): array {
        if (!is_file($this->path)) return [];
        $j = json_decode((string)file_get_contents($this->path), true);
        return is_array($j) ? $j : [];
    }

    public function add(string $url, ?string $secret): void {
        $this->assertSafeUrl($url);
        $items = $this->list();
        $items[] = ['url'=>$url] + ($secret ? ['secret'=>$secret] : []);
        $dir = dirname($this->path);
        if (!is_dir($dir)) @mkdir($dir, 0700, true);
        file_put_contents($this->path, json_encode($items, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
    }

    private function assertSafeUrl(string $url): void
    {
        $parts = parse_url($url);
        $scheme = $parts['scheme'] ?? '';
        $host = $parts['host'] ?? '';
        if ($scheme !== 'https' || $host === '') {
            throw new \InvalidArgumentException('webhook_url_invalid');
        }
        $lowerHost = strtolower($host);
        if ($lowerHost === 'localhost' || str_ends_with($lowerHost, '.local')) {
            throw new \InvalidArgumentException('webhook_url_forbidden');
        }
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
            if (!filter_var($host, FILTER_VALIDATE_IP, $flags)) {
                throw new \InvalidArgumentException('webhook_url_forbidden');
            }
        }
    }
}
