<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infra\Tag;

use App\ServiceInterface\Tag\TagEntityRepositoryInterface;
use PDO;

/**
 *
 */

/**
 *
 */
final readonly class PdoTagEntityRepository implements TagEntityRepositoryInterface
{
    /**
     * @param \PDO $pdo
     */
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @param string $tenant
     * @param string $id
     * @return array|null
     */
    public function findById(string $tenant, string $id): ?array
    {
        $sel = $this->pdo->prepare('SELECT id,slug,name,locale,weight,created_at,updated_at FROM tag_entity WHERE tenant=:t AND id=:id');
        $sel->execute([':t' => $tenant, ':id' => $id]);
        $row = $sel->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    /**
     * @param string $tenant
     * @param string $id
     * @param string $slug
     * @param string $name
     * @param string $locale
     * @param int $weight
     * @return array
     */
    public function create(string $tenant, string $id, string $slug, string $name, string $locale, int $weight): array
    {
        $stmt = $this->pdo->prepare('INSERT INTO tag_entity (id,tenant,slug,name,locale,weight) VALUES (:id,:t,:s,:n,:l,:w)');
        $stmt->execute([':id' => $id, ':t' => $tenant, ':s' => $slug, ':n' => $name, ':l' => $locale, ':w' => $weight]);
        return ['id' => $id, 'slug' => $slug, 'name' => $name, 'locale' => $locale, 'weight' => $weight];
    }

    /**
     * @param string $tenant
     * @param string $id
     * @param array $patch
     * @return void
     */
    public function patch(string $tenant, string $id, array $patch): void
    {
        $fields = [];
        $params = [':t' => $tenant, ':id' => $id];

        foreach (['name', 'locale', 'weight'] as $k) {
            if (array_key_exists($k, $patch)) {
                $fields[] = $k . ' = :' . $k;
                $params[':' . $k] = $k === 'weight' ? (int)$patch[$k] : (string)$patch[$k];
            }
        }
        if ($fields === []) {
            return;
        }

        $sql = 'UPDATE tag_entity SET ' . implode(',', $fields) . ' WHERE tenant=:t AND id=:id';
        $upd = $this->pdo->prepare($sql);
        $upd->execute($params);
    }

    /**
     * @param string $tenant
     * @param string $id
     * @return void
     */
    public function delete(string $tenant, string $id): void
    {
        $del = $this->pdo->prepare('DELETE FROM tag_entity WHERE tenant=:t AND id=:id');
        $del->execute([':t' => $tenant, ':id' => $id]);
    }
}
