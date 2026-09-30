<?php

declare(strict_types=1);

namespace App\Tagging\DTO\Write;

final readonly class TagClassificationRecord
{
    public function __construct(
        public string $id,
        public string $scope,
        public string $refId,
        public string $key,
        public string $value,
    ) {}
}
