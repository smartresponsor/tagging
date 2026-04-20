<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Http\Api\Tag;

use App\Tagging\Application\Write\Tag\Dto\CreateTagCommand;
use App\Tagging\Application\Write\Tag\Dto\DeleteTagCommand;
use App\Tagging\Application\Write\Tag\Dto\PatchTagCommand;
use App\Tagging\Application\Write\Tag\UseCase\CreateTagInterface;
use App\Tagging\Application\Write\Tag\UseCase\DeleteTagInterface;
use App\Tagging\Application\Write\Tag\UseCase\PatchTagInterface;
use App\Tagging\Http\Api\Tag\Responder\TagWriteResponder;
use App\Tagging\Service\Core\Tag\TagEntityQueryServiceInterface;

final readonly class TagController
{
    public function __construct(
        private TagEntityQueryServiceInterface $queryService,
        private CreateTagInterface $createTag,
        private PatchTagInterface $patchTag,
        private DeleteTagInterface $deleteTag,
        private TagWriteResponder $responder,
    ) {}

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function create(array $req): array
    {
        return $this->responder->respond(
            $this->createTag->execute(new CreateTagCommand(TagHttpRequest::tenant($req), TagHttpRequest::body($req))),
        );
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function get(array $req, string $id): array
    {
        try {
            $row = $this->queryService->get($this->tenant($req), $id);
            if (null === $row) {
                return $this->responder->bad('not_found', 404);
            }

            return $this->responder->ok($row);
        } catch (\InvalidArgumentException $e) {
            return $this->responder->bad($e->getMessage());
        }
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function patch(array $req, string $id): array
    {
        return $this->responder->respond(
            $this->patchTag->execute(
                new PatchTagCommand(TagHttpRequest::tenant($req), $id, TagHttpRequest::body($req)),
            ),
        );
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function delete(array $req, string $id): array
    {
        return $this->responder->respond(
            $this->deleteTag->execute(new DeleteTagCommand(TagHttpRequest::tenant($req), $id)),
        );
    }

    private function tenant(array $request): string
    {
        $tenant = TagHttpRequest::tenant($request);
        if ('' === $tenant) {
            throw new \InvalidArgumentException('invalid_tenant');
        }

        return $tenant;
    }
}
