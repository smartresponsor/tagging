<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Service\Tag;

final class TagNormalizer
{
    public static function normalizeLabel(string $label): string
    {
        $label = trim($label);
        if (class_exists(\Normalizer::class)) {
            $normalized = \Normalizer::normalize($label, \Normalizer::FORM_C);
            if ($normalized !== false && $normalized !== null) {
                $label = $normalized;
            }
        }
        return $label;
    }

    public static function slugify(string $label): string
    {
        $s = mb_strtolower(self::normalizeLabel($label));
        $s = preg_replace('/[^a-z0-9\-]+/u', '-', $s) ?? '';
        $s = preg_replace('/-+/', '-', $s) ?? '';
        return trim($s, '-');
    }
}
