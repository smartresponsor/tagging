<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Service\Core\TagDuplicateServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class TagDuplicateService extends AbstractTagService
{
    public function __construct(private TagDuplicateServiceInterface $duplicate) {}

    public function __invoke(Request $request, ?string $id = null, ?string $slug = null): Response
    {
        try {
            return $this->json([
                'ok' => true,
                'item' => $this->duplicate->duplicate(
                    $this->tenant($request),
                    $id,
                    $slug,
                    $this->payload($request),
                ),
            ], 201);
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
