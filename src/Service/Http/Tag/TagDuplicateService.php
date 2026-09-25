<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Service\Core\TagDuplicateServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TagDuplicateService extends TagAbstractService
{
    public function __construct(private TagDuplicateServiceInterface $duplicate) {}

    public function __invoke(Request $request, ?string $id = null, ?string $slug = null): Response
    {
        try {
            $id ??= $request->attributes->getString('id') ?: null;
            $slug ??= $request->attributes->getString('slug') ?: null;

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
