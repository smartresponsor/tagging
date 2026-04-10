<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag;

use App\Http\Api\Tag\Responder\TagAssignmentResponder;
use App\Service\Core\Tag\AssignOperationInterface;
use App\Service\Core\Tag\AssignService;
use App\Service\Core\Tag\UnassignOperationInterface;
use App\Service\Core\Tag\UnassignService;

final class AssignController
{
    /** @var string[] */
    private array $allowedTypes;

    private TagAssignmentResponder $responder;

    /**
     * @param AssignService   $assign
     * @param UnassignService $unassign
     */
    public function __construct(
        private readonly AssignOperationInterface $assign,
        private readonly UnassignOperationInterface $unassign,
        array $cfg = [],
    ) {
        $this->responder = new TagAssignmentResponder();
        $this->allowedTypes = $this->normalizeAllowedTypes($cfg['entity_types'] ?? ['*']);
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function assign(array $req, string $tagId): array
    {
        return $this->handleSingleOperation($req, $tagId, 'assign', ['duplicated' => false, 'conflict' => false]);
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function unassign(array $req, string $tagId): array
    {
        return $this->handleSingleOperation(
            $req,
            $tagId,
            'unassign',
            ['not_found' => false, 'duplicated' => false, 'conflict' => false],
        );
    }

    /**
     * Bulk operations (assign/unassign per operation).
     *
     * Payload:
     * {"operations":[
     *   {"op":"assign|unassign","tagId":"...","entityType":"...","entityId":"...","idem":"optional"},
     *   ...
     * ]}
     *
     * @return array{0:int,1:array<string,string>,2:string}
     */
    public function bulk(array $req): array
    {
        $tenant = $this->tenant($req);
        if (null === $tenant) {
            return $this->fail('invalid_tenant');
        }
        $body = TagHttpRequest::body($req);
        $ops = is_array($body['operations'] ?? null) ? $body['operations'] : [];

        $results = [];
        $done = 0;
        $errors = 0;

        foreach ($ops as $index => $op) {
            if (!is_array($op)) {
                ++$errors;
                $results[] = ['index' => $index, 'ok' => false, 'code' => 'validation_failed'];

                continue;
            }
            $optype = (string) ($op['op'] ?? '');
            $tagId = $this->tagId($op);
            $entity = $this->readEntity($op);

            if ('' === $optype || '' === $tagId || null === $entity) {
                ++$errors;
                $results[] = [
                    'index' => $index,
                    'op' => $optype,
                    'tagId' => $tagId,
                    'ok' => false,
                    'code' => 'validation_failed',
                ];

                continue;
            }
            $r = $this->dispatchOperation($optype, $tenant, $tagId, $entity, $this->idempotencyKey($op));
            if (null === $r) {
                ++$errors;
                $results[] = [
                    'index' => $index,
                    'op' => $optype,
                    'tagId' => $tagId,
                    'entityType' => $entity[0],
                    'entityId' => $entity[1],
                    'ok' => false,
                    'code' => 'validation_failed',
                ];

                continue;
            }

            if ($r['ok'] ?? false) {
                ++$done;
            } else {
                ++$errors;
            }

            $results[] = [
                'index' => $index,
                'op' => $optype,
                'tagId' => $tagId,
                'entityType' => $entity[0],
                'entityId' => $entity[1],
            ] + $r;
        }

        return $this->ok([
            'ok' => 0 === $errors,
            'processed' => count($ops),
            'done' => $done,
            'errors' => $errors,
            'results' => $results,
        ]);
    }

    /**
     * Bulk assign tags to a single entity.
     *
     * Payload:
     * {"entityType":"product","entityId":"...","tagIds":["...", ...]}
     *
     * @return array{0:int,1:array<string,string>,2:string}
     */
    public function assignBulkToEntity(array $req): array
    {
        $tenant = $this->tenant($req);
        if (null === $tenant) {
            return $this->fail('invalid_tenant');
        }
        $body = TagHttpRequest::body($req);

        $entity = $this->readEntity($body);
        $tagIds = $body['tagIds'] ?? ($body['tag_ids'] ?? null);
        if (!is_array($tagIds)) {
            $tagIds = [];
        }

        if (null === $entity || [] === $tagIds) {
            return $this->fail('validation_failed');
        }

        $items = [];
        $assigned = 0;
        $duplicated = 0;
        $errors = 0;

        foreach ($tagIds as $index => $tagId) {
            $tagId = trim((string) $tagId);
            if ('' === $tagId) {
                ++$errors;
                $items[] = ['index' => $index, 'ok' => false, 'code' => 'validation_failed'];

                continue;
            }

            $result = $this->assign->assign($tenant, $tagId, $entity[0], $entity[1]);
            if ($result['ok'] ?? false) {
                if ($result['duplicated'] ?? false) {
                    ++$duplicated;
                } else {
                    ++$assigned;
                }
            } else {
                ++$errors;
            }

            $items[] = ['index' => $index, 'tagId' => $tagId] + $result;
        }

        return $this->ok([
            'ok' => 0 === $errors,
            'entityType' => $entity[0],
            'entityId' => $entity[1],
            'processed' => count($tagIds),
            'assigned' => $assigned,
            'duplicated' => $duplicated,
            'errors' => $errors,
            'items' => $items,
        ]);
    }

    private function isAllowedType(string $etype): bool
    {
        if ('' === $etype) {
            return false;
        }
        if (in_array('*', $this->allowedTypes, true)) {
            return true;
        }

        return in_array($etype, $this->allowedTypes, true);
    }

    /**
     * @return list<string>
     */
    private function normalizeAllowedTypes(mixed $types): array
    {
        if (!is_array($types)) {
            return ['*'];
        }

        $normalized = [];
        foreach ($types as $type) {
            $type = strtolower(trim((string) $type));
            if ('' !== $type) {
                $normalized[] = $type;
            }
        }

        $normalized = array_values(array_unique($normalized));

        return [] !== $normalized ? $normalized : ['*'];
    }

    private static function normalizeEntityType(string $etype): string
    {
        $etype = strtolower(trim($etype));
        if ('' === $etype) {
            return '';
        }
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $etype)) {
            return '';
        }

        return $etype;
    }

    private static function normalizeEntityId(string $eid): string
    {
        $eid = trim($eid);
        if ('' === $eid) {
            return '';
        }
        if (strlen($eid) > 128) {
            return '';
        }

        return $eid;
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    private function ok(array $body): array
    {
        return $this->responder->success($body);
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    private function fail(string $code, array $body = []): array
    {
        return $this->failureForCode($code, $body);
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    private function failureForCode(string $code, array $body): array
    {
        return $this->responder->failure($code, $this->responder->statusForCode($code), $body);
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    private function handleSingleOperation(array $request, string $tagId, string $operation, array $defaults): array
    {
        $tenant = $this->tenant($request);
        if (null === $tenant) {
            return $this->fail('invalid_tenant');
        }

        $entity = $this->readEntity(TagHttpRequest::body($request));
        if (null === $entity) {
            return $this->fail('validation_failed');
        }

        $result = $this->dispatchOperation(
            $operation,
            $tenant,
            $tagId,
            $entity,
            $this->idempotencyKey(TagHttpRequest::body($request)),
        );
        if (null === $result) {
            return $this->fail('validation_failed');
        }

        return $this->assignmentResponse($result, $defaults);
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    private function assignmentResponse(array $result, array $defaults = []): array
    {
        $ok = (bool) ($result['ok'] ?? false);
        $code = isset($result['code']) ? (string) $result['code'] : null;
        $body = ['ok' => $ok];

        foreach ($defaults as $field => $default) {
            $body[$field] = $result[$field] ?? $default;
        }

        if (null !== $code && '' !== $code) {
            $body['code'] = $code;
        }

        if ($ok) {
            return $this->ok($body);
        }

        return $this->responder->failure($code ?? 'assign_failed', $this->responder->statusForCode($code), $body);
    }

    /** @return array{0:string,1:string}|null */
    private function readEntity(array $body): ?array
    {
        $etypeRaw = (string) ($body['entityType'] ?? ($body['entity_type'] ?? ''));
        $eidRaw = (string) ($body['entityId'] ?? ($body['entity_id'] ?? ''));
        $etype = self::normalizeEntityType($etypeRaw);
        $eid = self::normalizeEntityId($eidRaw);
        if ('' === $etype || '' === $eid) {
            return null;
        }
        if (!$this->isAllowedType($etype)) {
            return null;
        }

        return [$etype, $eid];
    }

    private function tenant(array $request): ?string
    {
        return TagHttpRequest::tenantOrNull($request);
    }

    private function tagId(array $payload): string
    {
        return trim((string) ($payload['tagId'] ?? ($payload['tag_id'] ?? '')));
    }

    private function idempotencyKey(array $payload): ?string
    {
        $idem = trim((string) ($payload['idem'] ?? ($payload['idemKey'] ?? '')));

        return '' !== $idem ? $idem : null;
    }

    /** @return array<string,mixed>|null */
    private function dispatchOperation(
        string $operation,
        string $tenant,
        string $tagId,
        array $entity,
        ?string $idem,
    ): ?array {
        return match ($operation) {
            'assign' => $this->assign->assign($tenant, $tagId, $entity[0], $entity[1], $idem),
            'unassign' => $this->unassign->unassign($tenant, $tagId, $entity[0], $entity[1], $idem),
            default => null,
        };
    }
}
