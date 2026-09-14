<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Service\Core\TagRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TagSynonymIndexService extends AbstractTagService
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
                'items' => $this->repository->listSynonyms($this->tenant($request), $tagId),
            ]);
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
