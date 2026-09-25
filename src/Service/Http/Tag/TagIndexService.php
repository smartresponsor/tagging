<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Service\Core\TagEntityQueryServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TagIndexService extends TagAbstractService
{
    public function __construct(private TagEntityQueryServiceInterface $query) {}

    public function __invoke(Request $request): Response
    {
        try {
            $limit = max(1, min(500, $request->query->getInt('limit', 100)));
            $offset = max(0, $request->query->getInt('offset'));

            return $this->json([
                'ok' => true,
                'items' => $this->query->index($this->tenant($request), $limit, $offset),
            ]);
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
