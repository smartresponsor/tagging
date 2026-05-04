<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Http\Api\Tag;

use App\Tagging\Application\Write\Tag\Dto\TagCreateCommand;
use App\Tagging\Application\Write\Tag\Dto\TagDeleteCommand;
use App\Tagging\Application\Write\Tag\Dto\TagPatchCommand;
use App\Tagging\Application\Write\Tag\UseCase\TagCreateUseCaseInterface;
use App\Tagging\Application\Write\Tag\UseCase\TagDeleteUseCaseInterface;
use App\Tagging\Application\Write\Tag\UseCase\TagPatchUseCaseInterface;
use App\Tagging\Http\Api\Tag\Responder\TagWriteResponder;
use App\Tagging\Service\Core\TagEntityQueryServiceInterface;

final readonly class TagController
{
    public function __construct(
        private TagEntityQueryServiceInterface $queryService,
        private TagCreateUseCaseInterface $createTag,
        private TagPatchUseCaseInterface $patchTag,
        private TagDeleteUseCaseInterface $deleteTag,
        private TagWriteResponder $responder,
    ) {}

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function create(array $req): array
    {
        return $this->responder->respond(
            $this->createTag->execute(new TagCreateCommand(TagHttpRequest::tenant($req), TagHttpRequest::body($req))),
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
                new TagPatchCommand(TagHttpRequest::tenant($req), $id, TagHttpRequest::body($req)),
            ),
        );
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function delete(array $req, string $id): array
    {
        return $this->responder->respond(
            $this->deleteTag->execute(new TagDeleteCommand(TagHttpRequest::tenant($req), $id)),
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
