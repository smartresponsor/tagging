<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Entity\Tag\TagRelationEntity;
use App\Tagging\Service\Core\TagRepositoryInterface;
use App\Tagging\Service\Core\TagUlidGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TagRelationCreateService extends AbstractTagService
{
    public function __construct(private TagRepositoryInterface $repository) {}

    public function __invoke(Request $request): Response
    {
        try {
            $tenant = $this->tenant($request);
            $payload = $this->payload($request);
            $fromId = trim((string) ($payload['fromTagId'] ?? ''));
            $toId = trim((string) ($payload['toTagId'] ?? ''));
            $type = trim((string) ($payload['type'] ?? ''));

            $from = $this->repository->getById($tenant, $fromId);
            $to = $this->repository->getById($tenant, $toId);

            if (null === $from || null === $to) {
                return $this->json(['ok' => false, 'code' => 'tag_not_found'], 404);
            }

            $relation = TagRelationEntity::create(TagUlidGenerator::generate(), $from, $to, $type);
            $this->repository->saveRelation($tenant, $relation);

            return $this->json([
                'ok' => true,
                'item' => [
                    'id' => $relation->id(),
                    'fromTagId' => $relation->fromTagId(),
                    'toTagId' => $relation->toTagId(),
                    'type' => $relation->type(),
                ],
            ], 201);
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
