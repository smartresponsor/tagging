<?php

declare(strict_types=1);

namespace App\Service\Core\Tag\Record;

final readonly class TagEntityCreateRecord
{
    public function __construct(
        public string $id,
        public string $slug,
        public string $name,
        public string $locale,
        public int $weight,
    ) {
    }

    /** @return array{id:string,slug:string,name:string,locale:string,weight:int} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'locale' => $this->locale,
            'weight' => $this->weight,
        ];
    }
}
