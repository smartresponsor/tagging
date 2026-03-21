<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag;

use App\Http\Api\Tag\Responder\TagAssignmentResponder;
use App\Service\Core\Tag\AssignOperationInterface;
use App\Service\Core\Tag\UnassignOperationInterface;

final class AssignController
{
    /** @var string[] */
    private array $allowedTypes;

    private TagAssignmentResponder $responder;

    /**
     * @param \App\Service\Core\Tag\AssignService   $assign
     * @param \App\Service\Core\Tag\UnassignService $unassign
     */
    public function __construct(private readonly AssignOperationInterface $assign, private readonly UnassignOperationInterface $unassign, array $cfg = [])
    {
        $this->responder = new TagAssignmentResponder();
        $types = $cfg['entity_types'] ?? ['*'];
        if (!is_array($types)) {
            $types = ['*'];
        }
        $norm = [];
        foreach ($types as $t) {
            $t = strtolower(trim((string) $t));
            if ('' !== $t) {
                $norm[] = $t;
            }
        }
        $norm = array_values(array_unique($norm));
        $this->allowedTypes = $norm ?: ['*'];
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function assign(array $req, string $tagId): array
    {
        $tenant = TagHttpRequest::tenant($req);
        if ('' === $tenant) {
            return $this->fail('invalid_tenant');
        }
        $body = TagHttpRequest::body($req);

        [$etype, $eid] = $this->readEntity($body);
        if ('' === $etype || '' === $eid) {
            return $this->fail('validation_failed');
        }

        $idem = (string) ($req['idemKey'] ?? '');
        $res = $this->assign->assign($tenant, $tagId, $etype, $eid, $idem ?: null);

        return $this->assignmentResponse($res, ['duplicated' => false, 'conflict' => false]);
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function unassign(array $req, string $tagId): array
    {
        $tenant = TagHttpRequest::tenant($req);
        if ('' === $tenant) {
            return $this->fail('invalid_tenant');
        }
        $body = TagHttpRequest::body($req);

        [$etype, $eid] = $this->readEntity($body);
        if ('' === $etype || '' === $eid) {
            return $this->fail('validation_failed');
        }

        $idem = (string) ($req['idemKey'] ?? '');
        $res = $this->unassign->unassign($tenant, $tagId, $etype, $eid, $idem ?: null);

        return $this->assignmentResponse($res, ['not_found' => false, 'duplicated' => false, 'conflict' => false]);
    }

    /**
     * Bulk operations (assign/unassign per operation).
     *
     * Payload:
     * {"operations":[{"op":"assign|unassign","tagId":"...","entityType":"...","entityId":"...","idem":"optional"}, ...]}
     *
     * @return array{0:int,1:array<string,string>,2:string}
     */
    public function bulk(array $req): array
    {
        $tenant = TagHttpRequest::tenant($req);
        if ('' === $tenant) {
            return $this->fail('invalid_tenant');
        }
        $body = TagHttpRequest::body($req);
        $ops = is_array($body['operations'] ?? null) ? $body['operations'] : [];

        $done = 0;
        $errors = 0;

        foreach ($ops as $op) {
            if (!is_array($op)) {
                ++$errors;
                continue;
            }
            $optype = (string) ($op['op'] ?? '');
            $tagId = (string) ($op['tagId'] ?? ($op['tag_id'] ?? ''));
            [$etype, $eid] = $this->readEntity($op);
            $idem = (string) ($op['idem'] ?? '');

            if ('' === $optype || '' === $tagId || '' === $etype || '' === $eid) {
                ++$errors;
                continue;
            }

            if ('assign' === $optype) {
                $r = $this->assign->assign($tenant, $tagId, $etype, $eid, $idem ?: null);
            } elseif ('unassign' === $optype) {
                $r = $this->unassign->unassign($tenant, $tagId, $etype, $eid, $idem ?: null);
            } else {
                ++$errors;
                continue;
            }

            $done += ($r['ok'] ?? false) ? 1 : 0;
        }

        return self::ok(['done' => $done, 'errors' => $errors]);
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
        $tenant = TagHttpRequest::tenant($req);
        if ('' === $tenant) {
            return $this->fail('invalid_tenant');
        }
        $body = TagHttpRequest::body($req);

        [$etype, $eid] = $this->readEntity($body);
        $tagIds = $body['tagIds'] ?? ($body['tag_ids'] ?? null);
        if (!is_array($tagIds)) {
            $tagIds = [];
        }

        if ('' === $etype || '' === $eid || [] === $tagIds) {
            return $this->fail('validation_failed');
        }

        $ok = 0;
        foreach ($tagIds as $tagId) {
            $tagId = (string) $tagId;
            if ('' === $tagId) {
                continue;
            }
            $r = $this->assign->assign($tenant, $tagId, $etype, $eid);
            $ok += ($r['ok'] ?? false) ? 1 : 0;
        }

        return self::ok(['ok' => true, 'assigned' => $ok]);
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
        return $this->responder->failure($code, $this->responder->statusForCode($code), $body);
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

    /** @return array{0:string,1:string} */
    private function readEntity(array $body): array
    {
        $etypeRaw = (string) ($body['entityType'] ?? ($body['entity_type'] ?? ''));
        $eidRaw = (string) ($body['entityId'] ?? ($body['entity_id'] ?? ''));
        $etype = self::normalizeEntityType($etypeRaw);
        $eid = self::normalizeEntityId($eidRaw);
        if ('' === $etype || '' === $eid) {
            return ['', ''];
        }
        if (!$this->isAllowedType($etype)) {
            return ['', ''];
        }

        return [$etype, $eid];
    }
}
