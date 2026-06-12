<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Application\Write\Tag\Dto\TagPatchCommand;
use App\Tagging\Application\Write\Tag\UseCase\TagPatchUseCaseInterface;
use App\Tagging\Service\Core\TagEntityQueryServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class TagUpdateService extends AbstractTagService
{
    public function __construct(
        private TagPatchUseCaseInterface $useCase,
        private TagEntityQueryServiceInterface $query,
    ) {}

    public function __invoke(Request $request, ?string $id = null, ?string $slug = null): Response
    {
        try {
            $tenant = $this->tenant($request);
            $id ??= (string) ($this->query->findBySlug($tenant, (string) $slug)['id'] ?? '');

            if ('' === $id) {
                return $this->json(['ok' => false, 'code' => 'not_found'], 404);
            }

            return $this->result($this->useCase->execute(new TagPatchCommand(
                tenant: $tenant,
                id: $id,
                payload: $this->payload($request),
            )));
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
