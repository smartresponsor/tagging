<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\Service\Tag;

use PDO;

final class IdempotencyStore
{
    public function __construct(private PDO $pdo) {}

    /** @return array{state:string,result?:array<string,mixed>} state: fresh|duplicate */
    public function begin(string $tenant, string $key, string $op, string $checksum): array
    {
        // Check existing record
        $sel = $this->pdo->prepare('SELECT status, result_json FROM idempotency_store WHERE tenant=:t AND key=:k');
        $sel->execute([':t'=>$tenant, ':k'=>$key]);
        $row = $sel->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            // Optional checksum verification can be added here
            $result = $row['result_json'] ? json_decode((string)$row['result_json'], true) : null;
            return ['state' => 'duplicate', 'result' => $result ?: []];
        }
        // Insert pending
        $ins = $this->pdo->prepare(
            'INSERT INTO idempotency_store (tenant, key, op, checksum, status, result_json)
             VALUES (:t,:k,:op,:c,:st,:res::jsonb)'
        );
        $ins->execute([
            ':t'=>$tenant, ':k'=>$key, ':op'=>$op, ':c'=>$checksum, ':st'=>'pending', ':res'=>json_encode([]),
        ]);
        return ['state' => 'fresh'];
    }

    /** @param array<string,mixed> $result */
    public function complete(string $tenant, string $key, array $result): void
    {
        $upd = $this->pdo->prepare(
            'UPDATE idempotency_store SET status=:st, result_json=:res::jsonb WHERE tenant=:t AND key=:k'
        );
        $upd->execute([
            ':st'=>'done',
            ':res'=>json_encode($result, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            ':t'=>$tenant,
            ':k'=>$key,
        ]);
    }
}
