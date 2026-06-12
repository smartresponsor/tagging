<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Service\Core\TagModerationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class TagProposalApproveService extends AbstractTagService
{
    public function __construct(private TagModerationService $moderation) {}

    public function __invoke(Request $request, string $id): Response
    {
        try {
            $decider = trim((string) (
                $request->attributes->get('actor')
                ?? $request->headers->get('X-Actor-Id', '')
            ));

            if ('' === $decider) {
                throw new \InvalidArgumentException('actor_required');
            }

            $this->moderation->approve($this->tenant($request), $id, $decider);

            return $this->json(['ok' => true, 'id' => $id]);
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
