<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Entity\Tag\TagSynonymEntity;
use App\Tagging\Service\Core\TagRepositoryInterface;
use App\Tagging\Service\Core\TagUlidGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TagSynonymCreateService extends TagAbstractService
{
    public function __construct(private TagRepositoryInterface $repository) {}

    public function __invoke(Request $request): Response
    {
        try {
            $tenant = $this->tenant($request);
            $payload = $this->payload($request);
            $tagId = trim((string) ($payload['tagId'] ?? ''));
            $label = trim((string) ($payload['label'] ?? ''));

            $tag = $this->repository->getById($tenant, $tagId);
            if (null === $tag) {
                return $this->json(['ok' => false, 'code' => 'tag_not_found'], 404);
            }

            $synonym = TagSynonymEntity::create(TagUlidGenerator::generate(), $tag, $label);
            $this->repository->saveSynonym($tenant, $synonym);

            return $this->json([
                'ok' => true,
                'item' => [
                    'id' => $synonym->id(),
                    'tagId' => $synonym->tagId(),
                    'label' => $synonym->label(),
                ],
            ], 201);
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
