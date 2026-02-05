<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Tag;

use App\Service\Tag\TagEntityService;
use PDOException;

final class TagController
{
    public function __construct(private TagEntityService $service)
    {
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function create(array $req): array
    {
        $tenant = (string)($req['headers']['x-tenant-id'] ?? '');
        $payload = is_array($req['body'] ?? null) ? $req['body'] : [];

        try {
            $created = $this->service->create($tenant, $payload);
            return self::ok(201, $created);
        } catch (\InvalidArgumentException $e) {
            return self::bad($e->getMessage());
        } catch (PDOException $e) {
            return self::conflict('conflict');
        }
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function get(array $req, string $id): array
    {
        $tenant = (string)($req['headers']['x-tenant-id'] ?? '');

        try {
            $row = $this->service->get($tenant, $id);
            if ($row === null) {
                return self::notFound();
            }
            return self::ok(200, $row);
        } catch (\InvalidArgumentException $e) {
            return self::bad($e->getMessage());
        }
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function patch(array $req, string $id): array
    {
        $tenant = (string)($req['headers']['x-tenant-id'] ?? '');
        $payload = is_array($req['body'] ?? null) ? $req['body'] : [];

        try {
            $this->service->patch($tenant, $id, $payload);
            return self::ok(200, ['id' => $id]);
        } catch (\InvalidArgumentException $e) {
            return self::bad($e->getMessage());
        }
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function delete(array $req, string $id): array
    {
        $tenant = (string)($req['headers']['x-tenant-id'] ?? '');

        try {
            $this->service->delete($tenant, $id);
            return [204, ['Content-Type' => 'application/json'], ''];
        } catch (\InvalidArgumentException $e) {
            return self::bad($e->getMessage());
        }
    }

    private static function ok(int $code, array $body): array
    {
        return [$code, ['Content-Type' => 'application/json'], json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}'];
    }

    private static function bad(string $code): array
    {
        return [400, ['Content-Type' => 'application/json'], json_encode(['code' => $code]) ?: '{"code":"validation_failed"}'];
    }

    private static function conflict(string $code): array
    {
        return [409, ['Content-Type' => 'application/json'], json_encode(['code' => $code]) ?: '{"code":"conflict"}'];
    }

    private static function notFound(): array
    {
        return [404, ['Content-Type' => 'application/json'], json_encode(['code' => 'not_found']) ?: '{"code":"not_found"}'];
    }
}
