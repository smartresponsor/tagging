<?php
declare(strict_types=1);
namespace App\Domain\Tag;

/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
final class TagSynonym {
    public function __construct(
        private string $id,
        private string $tagId,
        private string $label
    ){}
    public static function create(string $id, string $tagId, string $label): self {
        return new self($id,$tagId,$label);
    }
    public function id(): string {return $this->id;}
    public function tagId(): string {return $this->tagId;}
    public function label(): string {return $this->label;}
}
