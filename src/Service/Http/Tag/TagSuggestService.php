<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Service\Core\TagSuggestService as TagSuggestOperation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TagSuggestService extends TagAbstractService
{
    public function __construct(private TagSuggestOperation $suggest) {}

    public function __invoke(Request $request): Response
    {
        try {
            return $this->json([
                'ok' => true,
            ] + $this->suggest->suggest(
                $this->tenant($request),
                trim((string) $request->query->get('q', '')),
                max(1, min(50, $request->query->getInt('limit', 10))),
            ));
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
