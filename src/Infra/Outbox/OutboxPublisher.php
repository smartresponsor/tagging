<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\Infra\Outbox;

use PDO;

final class OutboxPublisher
{
    public function __construct(private PDO $pdo) {}

    /** @param array<string,mixed> $payload */
    public function publish(string $tenant, string $topic, array $payload): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO outbox_event (tenant, topic, payload) VALUES (:t, :topic, :payload::jsonb)'
        );
        $stmt->execute([
            ':t' => $tenant,
            ':topic' => $topic,
            ':payload' => json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
    }
}
