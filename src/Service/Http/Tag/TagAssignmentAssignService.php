<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Service\Core\TagAssignOperationInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class TagAssignmentAssignService extends AbstractTagService
{
    public function __construct(private TagAssignOperationInterface $assign) {}

    public function __invoke(Request $request): Response
    {
        try {
            $payload = $this->payload($request);
            $tagId = trim((string) ($payload['tagId'] ?? ''));
            $assignedType = trim((string) ($payload['assignedType'] ?? $payload['entityType'] ?? ''));
            $assignedId = trim((string) ($payload['assignedId'] ?? $payload['entityId'] ?? ''));

            if ('' === $tagId || '' === $assignedType || '' === $assignedId) {
                throw new \InvalidArgumentException('validation_failed');
            }

            $result = $this->assign->assign(
                $this->tenant($request),
                $tagId,
                $assignedType,
                $assignedId,
                isset($payload['idempotencyKey']) ? (string) $payload['idempotencyKey'] : null,
            );

            return $this->json($result, ($result['ok'] ?? false) ? 200 : 409);
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
