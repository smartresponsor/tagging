<?php

declare(strict_types=1);

namespace App\Service\Core\Tag;

final readonly class TagIdempotencyRequest
{
    public function __construct(
        public string $tenant,
        public string $action,
        public string $tagId,
        public string $entityType,
        public string $entityId,
        public ?string $idempotencyKey,
    ) {}
}
