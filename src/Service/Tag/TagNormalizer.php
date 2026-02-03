<?php
declare(strict_types=1);
namespace App\Service\Tag;

/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
final class TagNormalizer {
    public static function normalizeLabel(string $label): string {
        $label = trim($label);
        $label = \Normalizer::normalize($label, \Normalizer::FORM_C);
        return $label;
    }
    public static function slugify(string $label): string {
        $s = mb_strtolower(self::normalizeLabel($label));
        $s = preg_replace('/[^a-z0-9\-]+/u','-', $s);
        $s = trim($s ?? '', '-');
        return $s;
    }
}
