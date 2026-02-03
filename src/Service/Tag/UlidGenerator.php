<?php
declare(strict_types=1);
namespace App\Service\Tag;

/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Minimal ULID generator compatible with PHP 8.2 (no external deps).
 */
final class UlidGenerator {
    public static function generate(): string {
        $t = microtime(true);
        $ms = (int)($t * 1000);
        $rand = bin2hex(random_bytes(10));
        return sprintf('%013d-%s', $ms, $rand);
    }
}
