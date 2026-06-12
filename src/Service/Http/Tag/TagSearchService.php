<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Service\Core\TagSearchService as TagSearchOperation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class TagSearchService extends AbstractTagService
{
    public function __construct(private TagSearchOperation $search) {}

    public function __invoke(Request $request): Response
    {
        try {
            return $this->json([
                'ok' => true,
            ] + $this->search->search(
                $this->tenant($request),
                trim((string) $request->query->get('q', '')),
                max(1, min(100, $request->query->getInt('pageSize', 20))),
                $request->query->get('pageToken'),
            ));
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
