<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core\Tag;

final readonly class IdempotencyStore
{
    public function __construct(private \PDO $pdo) {}

    /** @return array{state:string,result?:array<string,mixed>} state: fresh|duplicate */
    public function begin(string $tenant, string $key, string $op, string $checksum): array
    {
        // Check existing record
        $sel = $this->pdo->prepare(
            'SELECT status, checksum, result_json FROM idempotency_store WHERE tenant=:t AND key=:k',
        );
        $sel->execute([':t' => $tenant, ':k' => $key]);
        $row = $sel->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            if ((string) ($row['checksum'] ?? '') !== $checksum) {
                return ['state' => 'conflict', 'result' => ['code' => 'idempotency_conflict']];
            }

            $result = $row['result_json'] ? json_decode((string) $row['result_json'], true) : null;

            return ['state' => 'duplicate', 'result' => is_array($result) ? $result : []];
        }
        // Insert pending
        $ins = $this->pdo->prepare(
            'INSERT INTO idempotency_store (tenant, key, op, checksum, status, result_json)
             VALUES (:t,:k,:op,:c,:st,CAST(:res AS jsonb))',
        );
        $ins->execute([
            ':t' => $tenant,
            ':k' => $key,
            ':op' => $op,
            ':c' => $checksum,
            ':st' => 'pending',
            ':res' => json_encode([]),
        ]);

        return ['state' => 'fresh'];
    }

    /** @param array<string,mixed> $result */
    public function complete(string $tenant, string $key, array $result): void
    {
        $upd = $this->pdo->prepare(
            'UPDATE idempotency_store SET status=:st, result_json=CAST(:res AS jsonb) WHERE tenant=:t AND key=:k',
        );
        $upd->execute([
            ':st' => 'done',
            ':res' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ':t' => $tenant,
            ':k' => $key,
        ]);
    }
}
