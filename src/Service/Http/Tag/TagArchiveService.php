<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Service\Core\TagLifecycleServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TagArchiveService extends TagAbstractService
{
    public function __construct(private TagLifecycleServiceInterface $lifecycle) {}

    public function __invoke(Request $request, ?string $id = null, ?string $slug = null): Response
    {
        try {
            $id ??= $request->attributes->getString('id') ?: null;
            $slug ??= $request->attributes->getString('slug') ?: null;

            return $this->json([
                'ok' => true,
                'item' => $this->lifecycle->archive($this->tenant($request), $id, $slug),
            ]);
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
