<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Tag;

use App\Service\Core\Tag\Record\TagEntityCreateRecord;
use App\Service\Core\Tag\TagEntityRepositoryInterface;

final readonly class PdoTagEntityRepository implements TagEntityRepositoryInterface
{
    public function __construct(private \PDO $pdo) {}

    public function findById(string $tenant, string $id): ?array
    {
        $sel = $this->pdo->prepare(
            'SELECT id,slug,name,locale,weight,created_at,updated_at FROM tag_entity WHERE tenant=:t AND id=:id',
        );
        $sel->execute([':t' => $tenant, ':id' => $id]);
        $row = $sel->fetch(\PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function create(string $tenant, TagEntityCreateRecord $record): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO tag_entity (id,tenant,slug,name,locale,weight) VALUES (:id,:t,:s,:n,:l,:w)',
        );
        $stmt->execute([
            ':id' => $record->id,
            ':t' => $tenant,
            ':s' => $record->slug,
            ':n' => $record->name,
            ':l' => $record->locale,
            ':w' => $record->weight,
        ]);

        return $record->toArray();
    }

    public function patch(string $tenant, string $id, array $patch): void
    {
        $fields = [];
        $params = [':t' => $tenant, ':id' => $id];

        foreach (['name', 'locale', 'weight'] as $k) {
            if (array_key_exists($k, $patch)) {
                $fields[] = $k . ' = :' . $k;
                $params[':' . $k] = 'weight' === $k ? (int) $patch[$k] : (string) $patch[$k];
            }
        }
        if ([] === $fields) {
            return;
        }

        $sql = 'UPDATE tag_entity SET ' . implode(',', $fields) . ' WHERE tenant=:t AND id=:id';
        $upd = $this->pdo->prepare($sql);
        $upd->execute($params);
    }

    public function delete(string $tenant, string $id): void
    {
        $del = $this->pdo->prepare('DELETE FROM tag_entity WHERE tenant=:t AND id=:id');
        $del->execute([':t' => $tenant, ':id' => $id]);
    }
}
