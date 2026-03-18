<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag;

use App\Application\Write\Tag\Dto\CreateTagCommand;
use App\Application\Write\Tag\Dto\DeleteTagCommand;
use App\Application\Write\Tag\Dto\PatchTagCommand;
use App\Application\Write\Tag\UseCase\CreateTag;
use App\Application\Write\Tag\UseCase\DeleteTag;
use App\Application\Write\Tag\UseCase\PatchTag;
use App\Http\Api\Tag\Responder\TagWriteResponder;
use App\Service\Core\Tag\TagEntityService;

final readonly class TagController
{
    public function __construct(
        private TagEntityService $queryService,
        private CreateTag $createTag,
        private PatchTag $patchTag,
        private DeleteTag $deleteTag,
        private TagWriteResponder $responder,
    ) {
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function create(array $req): array
    {
        $tenant = (string) ($req['headers']['x-tenant-id'] ?? '');
        $payload = is_array($req['body'] ?? null) ? $req['body'] : [];

        return $this->responder->respond($this->createTag->execute(new CreateTagCommand($tenant, $payload)));
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function get(array $req, string $id): array
    {
        $tenant = (string) ($req['headers']['x-tenant-id'] ?? '');

        try {
            $row = $this->queryService->get($tenant, $id);
            if (null === $row) {
                return self::notFound();
            }

            return self::ok($row);
        } catch (\InvalidArgumentException $e) {
            return self::bad($e->getMessage());
        }
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function patch(array $req, string $id): array
    {
        $tenant = (string) ($req['headers']['x-tenant-id'] ?? '');
        $payload = is_array($req['body'] ?? null) ? $req['body'] : [];

        return $this->responder->respond($this->patchTag->execute(new PatchTagCommand($tenant, $id, $payload)));
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function delete(array $req, string $id): array
    {
        $tenant = (string) ($req['headers']['x-tenant-id'] ?? '');

        return $this->responder->respond($this->deleteTag->execute(new DeleteTagCommand($tenant, $id)));
    }

    private static function ok(array $body): array
    {
        return [200, ['Content-Type' => 'application/json'], json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}'];
    }

    private static function bad(string $code): array
    {
        return [400, ['Content-Type' => 'application/json'], json_encode(['code' => $code]) ?: '{"code":"validation_failed"}'];
    }

    private static function notFound(): array
    {
        return [404, ['Content-Type' => 'application/json'], json_encode(['code' => 'not_found']) ?: '{"code":"not_found"}'];
    }
}
