<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Service\Core\TagModerationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TagProposalCreateService extends AbstractTagService
{
    public function __construct(private TagModerationService $moderation) {}

    public function __invoke(Request $request): Response
    {
        try {
            $payload = $this->payload($request);
            $type = trim((string) ($payload['type'] ?? ''));

            if ('' === $type) {
                throw new \InvalidArgumentException('proposal_type_required');
            }

            return $this->json([
                'ok' => true,
                'id' => $this->moderation->propose(
                    $this->tenant($request),
                    $type,
                    is_array($payload['payload'] ?? null) ? $payload['payload'] : [],
                ),
            ], 201);
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
