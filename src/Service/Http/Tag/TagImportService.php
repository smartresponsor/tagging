<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Command\Input\TagCreateCommand;
use App\Tagging\HandlerInterface\Write\TagCreateHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TagImportService extends TagAbstractService
{
    public function __construct(private TagCreateHandlerInterface $create) {}

    public function __invoke(Request $request): Response
    {
        try {
            $tenant = $this->tenant($request);
            $items = $this->payload($request)['items'] ?? [];

            if (!is_array($items)) {
                throw new \InvalidArgumentException('invalid_items');
            }

            $results = [];

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $result = $this->create->execute(new TagCreateCommand($tenant, $item));
                $results[] = [
                    'ok' => $result->ok,
                    'status' => $result->status,
                    'item' => $result->payload,
                    'code' => $result->error?->value,
                ];
            }

            return $this->json(['ok' => true, 'results' => $results]);
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
