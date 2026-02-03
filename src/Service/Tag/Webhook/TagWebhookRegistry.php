<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
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
        $items = $this->list();
        $items[] = ['url'=>$url] + ($secret ? ['secret'=>$secret] : []);
        $dir = dirname($this->path);
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
        file_put_contents($this->path, json_encode($items, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
    }
}
