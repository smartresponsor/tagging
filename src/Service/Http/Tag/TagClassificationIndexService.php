<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Service\Core\TagRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class TagClassificationIndexService extends AbstractTagService
{
    public function __construct(private TagRepositoryInterface $repository) {}

    public function __invoke(Request $request): Response
    {
        try {
            $scope = trim((string) $request->query->get('scope', ''));
            $refId = trim((string) $request->query->get('refId', ''));

            if ('' === $scope || '' === $refId) {
                throw new \InvalidArgumentException('classification_selector_required');
            }

            return $this->json([
                'ok' => true,
                'items' => $this->repository->listClassifications(
                    $this->tenant($request),
                    $scope,
                    $refId,
                ),
            ]);
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
