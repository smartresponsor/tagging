<?php
declare(strict_types=1);
namespace App\Domain\Tag;

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
final class TagRelation {
    public function __construct(
        private string $id,
        private string $fromTagId,
        private string $toTagId,
        private string $type // 'broader' | 'related'
    ){}
    public static function create(string $id, string $fromTagId, string $toTagId, string $type): self {
        if (!in_array($type, ['broader','related'], true)) throw new \InvalidArgumentException('invalid relation type');
        return new self($id,$fromTagId,$toTagId,$type);
    }
    public function id(): string {return $this->id;}
    public function fromTagId(): string {return $this->fromTagId;}
    public function toTagId(): string {return $this->toTagId;}
    public function type(): string {return $this->type;}
}
