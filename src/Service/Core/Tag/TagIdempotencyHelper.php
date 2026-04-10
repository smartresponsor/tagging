<?php

declare(strict_types=1);

namespace App\Service\Core\Tag;

final class TagIdempotencyHelper
{
    private function __construct()
    {
    }

    /**
     * @return array{ok:bool, duplicated?:bool, conflict?:bool, code?:string, not_found?:bool}|null
     */
    public static function begin(
        ?IdempotencyStore $store,
        string $tenant,
        string $action,
        string $tagId,
        string $entityType,
        string $entityId,
        ?string $idempotencyKey,
    ): ?array {
        if (null === $idempotencyKey || '' === $idempotencyKey || null === $store) {
            return null;
        }

        $checksum = hash('sha256', implode('|', [$tenant, $tagId, $entityType, $entityId]));
        $state = $store->begin($tenant, $idempotencyKey, $action, $checksum);

        return match ($state['state'] ?? null) {
            'duplicate' => self::duplicateResult($state),
            'conflict' => ['ok' => false, 'conflict' => true, 'code' => 'idempotency_conflict'],
            default => null,
        };
    }

    /** @param array<string,mixed> $state
     * @return array{ok:bool, duplicated:bool, conflict?:bool, code?:string, not_found?:bool}
     */
    public static function duplicateResult(array $state): array
    {
        $result = is_array($state['result'] ?? null) ? $state['result'] : ['ok' => true];
        $result['duplicated'] = true;

        return $result;
    }
}
