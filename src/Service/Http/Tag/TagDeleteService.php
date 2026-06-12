<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Application\Write\Tag\Dto\TagDeleteCommand;
use App\Tagging\Application\Write\Tag\UseCase\TagDeleteUseCaseInterface;
use App\Tagging\Service\Core\TagEntityQueryServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class TagDeleteService extends AbstractTagService
{
    public function __construct(
        private TagDeleteUseCaseInterface $useCase,
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

            return $this->result($this->useCase->execute(new TagDeleteCommand($tenant, $id)));
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
