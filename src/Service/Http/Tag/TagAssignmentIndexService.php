<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\RepositoryInterface\TagReadModelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TagAssignmentIndexService extends TagAbstractService
{
    public function __construct(private TagReadModelInterface $read) {}

    public function __invoke(Request $request): Response
    {
        try {
            $tenant = $this->tenant($request);
            $tagId = trim((string) $request->query->get('tagId', ''));
            $assignedType = trim((string) $request->query->get('assignedType', ''));
            $assignedId = trim((string) $request->query->get('assignedId', ''));
            $limit = max(1, min(500, $request->query->getInt('limit', 100)));

            if ('' !== $tagId) {
                return $this->json([
                    'ok' => true,
                    'tagId' => $tagId,
                    'items' => $this->read->linksForTag($tenant, $tagId, $limit),
                ]);
            }

            if ('' === $assignedType || '' === $assignedId) {
                throw new \InvalidArgumentException('assignment_selector_required');
            }

            return $this->json([
                'ok' => true,
                'assignedType' => $assignedType,
                'assignedId' => $assignedId,
                'items' => $this->read->tagsForEntity($tenant, $assignedType, $assignedId, $limit),
            ]);
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
