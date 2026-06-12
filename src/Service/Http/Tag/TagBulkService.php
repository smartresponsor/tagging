<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Service\Core\TagAssignOperationInterface;
use App\Tagging\Service\Core\TagUnassignOperationInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class TagBulkService extends AbstractTagService
{
    public function __construct(
        private TagAssignOperationInterface $assign,
        private TagUnassignOperationInterface $unassign,
    ) {}

    public function __invoke(Request $request): Response
    {
        try {
            $tenant = $this->tenant($request);
            $operations = $this->payload($request)['operations'] ?? [];

            if (!is_array($operations)) {
                throw new \InvalidArgumentException('invalid_operations');
            }

            $results = [];

            foreach ($operations as $operation) {
                if (!is_array($operation)) {
                    continue;
                }

                $arguments = [
                    $tenant,
                    (string) ($operation['tagId'] ?? ''),
                    (string) ($operation['assignedType'] ?? $operation['entityType'] ?? ''),
                    (string) ($operation['assignedId'] ?? $operation['entityId'] ?? ''),
                ];

                $results[] = 'unassign' === ($operation['operation'] ?? $operation['op'] ?? null)
                    ? $this->unassign->unassign(...$arguments)
                    : $this->assign->assign(...$arguments);
            }

            return $this->json(['ok' => true, 'results' => $results]);
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
