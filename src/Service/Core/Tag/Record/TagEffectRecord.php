<?php

declare(strict_types=1);

namespace App\Tagging\Service\Core\Tag\Record;

final readonly class TagEffectRecord
{
    public function __construct(
        public string $id,
        public string $assignedType,
        public string $assignedId,
        public string $key,
        public string $value,
        public string $sourceScope,
        public string $sourceId,
    ) {}
}
