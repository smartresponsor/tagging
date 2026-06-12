<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Service\Core\TagRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class TagSchemeShowService extends AbstractTagService
{
    public function __construct(private TagRepositoryInterface $repository) {}

    public function __invoke(Request $request, string $name): Response
    {
        try {
            $tenant = $this->tenant($request);
            $scheme = $this->repository->getSchemeByName($tenant, $name);

            if (null === $scheme) {
                return $this->json(['ok' => false, 'code' => 'not_found'], 404);
            }

            return $this->json([
                'ok' => true,
                'item' => [
                    'id' => $scheme->id(),
                    'name' => $scheme->nameEntity(),
                    'locale' => $scheme->locale(),
                    'tagIds' => $this->repository->listTagsByScheme($tenant, $name),
                ],
            ]);
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
