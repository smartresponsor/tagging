<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Tag\Slug;

/**
 *
 */

/**
 *
 */
final class Slugifier
{
    /** @var array<string,string> */
    private array $map = [
        // Ukrainian/Russian basic transliteration
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'H', 'Ґ' => 'G', 'Д' => 'D', 'Е' => 'E', 'Є' => 'Ye', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'Y', 'І' => 'I', 'Ї' => 'Yi', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'Kh', 'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shch', 'Ь' => '', 'Ю' => 'Yu', 'Я' => 'Ya',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'h', 'ґ' => 'g', 'д' => 'd', 'е' => 'e', 'є' => 'ie', 'ж' => 'zh', 'з' => 'z', 'и' => 'y', 'і' => 'i', 'ї' => 'i', 'й' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ь' => '', 'ю' => 'iu', 'я' => 'ia',
        'Ё' => 'Yo', 'ё' => 'yo', 'Ъ' => '', 'ъ' => '', 'Ы' => 'y', 'ы' => 'y', 'Э' => 'e', 'э' => 'e',
        // accents & symbols
        '’' => "'", 'ʼ' => "'", '`' => "'", '´' => "'", '“' => '"', '”' => '"', '«' => '"', '»' => '"',
    ];

    /**
     * @param bool $lowercase
     * @param int $maxLen
     */
    public function __construct(private readonly bool $lowercase = true, private readonly int $maxLen = 64)
    {
    }

    /**
     * @param string $s
     * @return string
     */
    public function slugify(string $s): string
    {
        // Transliterate basic
        $t = strtr($s, $this->map);
        // Replace non-word with dash
        $t = preg_replace('/[^A-Za-z0-9]+/u', '-', $t) ?? '';
        // Collapse dashes
        $t = preg_replace('/-+/', '-', $t) ?? '';
        // Trim dashes
        $t = trim($t, '-');
        if ($this->lowercase) $t = strtolower($t);
        if ($this->maxLen > 0 && strlen($t) > $this->maxLen) $t = substr($t, 0, $this->maxLen);
        return $t;
    }
}
