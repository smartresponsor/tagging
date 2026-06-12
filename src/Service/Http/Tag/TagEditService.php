<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Form\Tag\TagUpdateType;
use App\Tagging\Service\Core\TagEntityQueryServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class TagEditService extends AbstractTagService
{
    public function __construct(private TagEntityQueryServiceInterface $query) {}

    public function __invoke(Request $request, ?string $id = null, ?string $slug = null): Response
    {
        try {
            $item = null !== $id
                ? $this->query->findById($this->tenant($request), $id)
                : $this->query->findBySlug($this->tenant($request), (string) $slug);

            return null === $item
                ? $this->json(['ok' => false, 'code' => 'not_found'], 404)
                : $this->json([
                    'ok' => true,
                    'item' => $item,
                    'type' => TagUpdateType::class,
                ]);
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
