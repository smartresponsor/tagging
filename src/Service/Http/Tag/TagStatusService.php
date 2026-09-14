<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use Symfony\Component\HttpFoundation\JsonResponse;

final readonly class TagStatusService
{
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'ok' => true,
            'service' => 'tagging',
            'surface' => 'zero-controller',
            'rootEntity' => 'App\\Tagging\\Entity\\Tag\\TagEntity',
        ]);
    }
}
