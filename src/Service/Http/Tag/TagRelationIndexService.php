<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Service\Core\TagRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class TagRelationIndexService extends AbstractTagService
{
    public function __construct(private TagRepositoryInterface $repository) {}

    public function __invoke(Request $request): Response
    {
        try {
            $tagId = trim((string) $request->query->get('tagId', ''));
            if ('' === $tagId) {
                throw new \InvalidArgumentException('tag_id_required');
            }

            return $this->json([
                'ok' => true,
                'items' => $this->repository->listRelations(
                    $this->tenant($request),
                    $tagId,
                    ($type = trim((string) $request->query->get('type', ''))) !== '' ? $type : null,
                ),
            ]);
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
