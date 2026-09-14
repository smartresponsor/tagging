<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Form\Tag\TagCreateType;
use Symfony\Component\HttpFoundation\JsonResponse;

final readonly class TagNewService
{
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'ok' => true,
            'type' => TagCreateType::class,
        ]);
    }
}
