<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Domain\Tag;

final class TagScheme {
    public function __construct(
        private string $id,
        private string $name,
        private ?string $locale,
        private \DateTimeImmutable $createdAt
    ){}
    public static function create(string $id, string $name, ?string $locale): self {
        return new self($id,$name,$locale,new \DateTimeImmutable());
    }
    public function id(): string {return $this->id;}
    public function name(): string {return $this->name;}
    public function locale(): ?string {return $this->locale;}
    public function createdAt(): \DateTimeImmutable {return $this->createdAt;}
}
