<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\Http\Tag;

use PDO;
use App\Service\Tag\Slug\{Slugifier,SlugPolicy};

final class TagController
{
    public function __construct(private PDO $pdo, private Slugifier $slugifier)
    { }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function create(array $req): array
    {
        $tenant = (string)($req['headers']['x-tenant-id'] ?? '');
        if ($tenant === '') return self::bad('invalid_tenant');
        $payload = is_array($req['body'] ?? null) ? $req['body'] : [];
        $slug = (string)($payload['slug'] ?? '');
        $name = (string)($payload['name'] ?? '');
        $locale = $payload['locale'] ?? 'en';
        $weight = (int)($payload['weight'] ?? 0);
        if ($name === '') return self::bad('validation_failed');

        $policy = new SlugPolicy($this->pdo, $this->slugifier);
        if ($slug === '') $slug = $policy->make($tenant, $name);
        if (!$policy->validate($slug)) return self::bad('validation_failed');

        $id = self::ulid();
        $stmt = $this->pdo->prepare('INSERT INTO tag_entity (id,tenant,slug,name,locale,weight) VALUES (:id,:t,:s,:n,:l,:w)');
        try {
            $stmt->execute([':id'=>$id, ':t'=>$tenant, ':s'=>$slug, ':n'=>$name, ':l'=>$locale, ':w'=>$weight]);
        } catch (\Throwable $e) {
            return self::conflict('conflict');
        }
        return self::ok(201, ['id'=>$id,'slug'=>$slug,'name'=>$name,'locale'=>$locale,'weight'=>$weight]);
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function get(array $req, string $id): array
    {
        $tenant = (string)($req['headers']['x-tenant-id'] ?? '');
        if ($tenant === '') return self::bad('invalid_tenant');
        $sel = $this->pdo->prepare('SELECT id,slug,name,locale,weight,created_at,updated_at FROM tag_entity WHERE tenant=:t AND id=:id');
        $sel->execute([':t'=>$tenant, ':id'=>$id]);
        $row = $sel->fetch(PDO::FETCH_ASSOC);
        if (!$row) return self::notFound();
        return self::ok(200, $row);
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function patch(array $req, string $id): array
    {
        $tenant = (string)($req['headers']['x-tenant-id'] ?? '');
        if ($tenant === '') return self::bad('invalid_tenant');
        $payload = is_array($req['body'] ?? null) ? $req['body'] : [];
        $fields = [];
        $params = [':t'=>$tenant, ':id'=>$id];
        foreach (['name','locale','weight'] as $k) {
            if (array_key_exists($k, $payload)) {
                $fields[] = $k.' = :'.$k;
                $params[':'.$k] = $k==='weight' ? (int)$payload[$k] : (string)$payload[$k];
            }
        }
        if (!$fields) return self::ok(200, ['id'=>$id]);
        $sql = 'UPDATE tag_entity SET '.implode(',', $fields).' WHERE tenant=:t AND id=:id';
        $upd = $this->pdo->prepare($sql);
        $upd->execute($params);
        return self::ok(200, ['id'=>$id]);
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function delete(array $req, string $id): array
    {
        $tenant = (string)($req['headers']['x-tenant-id'] ?? '');
        if ($tenant === '') return self::bad('invalid_tenant');
        $del = $this->pdo->prepare('DELETE FROM tag_entity WHERE tenant=:t AND id=:id');
        $del->execute([':t'=>$tenant, ':id'=>$id]);
        return [204, ['Content-Type'=>'application/json'], ''];
    }

    private static function ok(int $code, array $body): array
    { return [$code, ['Content-Type'=>'application/json'], json_encode($body)]; }

    private static function bad(string $code): array
    { return [400, ['Content-Type'=>'application/json'], json_encode(['code'=>$code])]; }

    private static function conflict(string $code): array
    { return [409, ['Content-Type'=>'application/json'], json_encode(['code'=>$code])]; }

    private static function notFound(): array
    { return [404, ['Content-Type'=>'application/json'], json_encode(['code'=>'not_found'])]; }

    private static function ulid(): string
    {
        // simple ULID-ish; replace with real ULID lib in prod
        return substr(strtoupper(bin2hex(random_bytes(13))), 0, 26);
    }
}
