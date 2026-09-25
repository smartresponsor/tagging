<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Service\Core\TagEntityQueryServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TagExportService extends TagAbstractService
{
    public function __construct(private TagEntityQueryServiceInterface $query) {}

    public function __invoke(Request $request): Response
    {
        try {
            return $this->json([
                'ok' => true,
                'items' => $this->query->index($this->tenant($request), 5000, 0),
            ]);
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
