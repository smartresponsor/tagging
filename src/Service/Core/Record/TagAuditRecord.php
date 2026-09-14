<?php

declare(strict_types=1);

namespace App\Tagging\Service\Core\Record;

final readonly class TagAuditRecord
{
    public function __construct(
        public string $id,
        public string $action,
        public string $entityType,
        public string $entityId,
        public string $detailsJson,
    ) {}
}
