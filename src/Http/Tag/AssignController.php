<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Tag;

use App\Service\Tag\{AssignService, UnassignService};

/**
 *
 */

/**
 *
 */
final class AssignController
{
    /** @var string[] */
    private array $allowedTypes;

    /**
     * @param \App\Service\Tag\AssignService $assign
     * @param \App\Service\Tag\UnassignService $unassign
     * @param array $cfg
     */
    public function __construct(private readonly AssignService $assign, private readonly UnassignService $unassign, array $cfg = [])
    {
        $types = $cfg['entity_types'] ?? ['*'];
        if (!is_array($types)) $types = ['*'];
        $norm = [];
        foreach ($types as $t) {
            $t = strtolower(trim((string)$t));
            if ($t !== '') $norm[] = $t;
        }
        $norm = array_values(array_unique($norm));
        $this->allowedTypes = $norm ?: ['*'];
    }


    /** @return array{0:int,1:array<string,string>,2:string} */
    public function assign(array $req, string $tagId): array
    {
        $tenant = (string)($req['headers']['x-tenant-id'] ?? '');
        if ($tenant === '') return self::bad('invalid_tenant');
        $body = is_array($req['body'] ?? null) ? $req['body'] : [];

        [$etype, $eid] = $this->readEntity($body);
        if ($etype === '' || $eid === '') return self::bad('validation_failed');

        $idem = (string)($req['idemKey'] ?? '');
        $res = $this->assign->assign($tenant, $tagId, $etype, $eid, $idem ?: null);
        return self::ok(['ok' => $res['ok'] ?? false, 'duplicated' => $res['duplicated'] ?? false]);
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function unassign(array $req, string $tagId): array
    {
        $tenant = (string)($req['headers']['x-tenant-id'] ?? '');
        if ($tenant === '') return self::bad('invalid_tenant');
        $body = is_array($req['body'] ?? null) ? $req['body'] : [];

        [$etype, $eid] = $this->readEntity($body);
        if ($etype === '' || $eid === '') return self::bad('validation_failed');

        $idem = (string)($req['idemKey'] ?? '');
        $res = $this->unassign->unassign($tenant, $tagId, $etype, $eid, $idem ?: null);
        return self::ok(['ok' => $res['ok'] ?? false, 'not_found' => $res['not_found'] ?? false]);
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
        $tenant = (string)($req['headers']['x-tenant-id'] ?? '');
        if ($tenant === '') return self::bad('invalid_tenant');
        $body = is_array($req['body'] ?? null) ? $req['body'] : [];
        $ops = is_array($body['operations'] ?? null) ? $body['operations'] : [];

        $done = 0;
        $errors = 0;

        foreach ($ops as $op) {
            if (!is_array($op)) {
                $errors++;
                continue;
            }
            $optype = (string)($op['op'] ?? '');
            $tagId = (string)($op['tagId'] ?? ($op['tag_id'] ?? ''));
            [$etype, $eid] = $this->readEntity($op);
            $idem = (string)($op['idem'] ?? '');

            if ($optype === '' || $tagId === '' || $etype === '' || $eid === '') {
                $errors++;
                continue;
            }

            if ($optype === 'assign') {
                $r = $this->assign->assign($tenant, $tagId, $etype, $eid, $idem ?: null);
            } elseif ($optype === 'unassign') {
                $r = $this->unassign->unassign($tenant, $tagId, $etype, $eid, $idem ?: null);
            } else {
                $errors++;
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
        $tenant = (string)($req['headers']['x-tenant-id'] ?? '');
        if ($tenant === '') return self::bad('invalid_tenant');
        $body = is_array($req['body'] ?? null) ? $req['body'] : [];

        [$etype, $eid] = $this->readEntity($body);
        $tagIds = $body['tagIds'] ?? ($body['tag_ids'] ?? null);
        if (!is_array($tagIds)) $tagIds = [];

        if ($etype === '' || $eid === '' || $tagIds === []) return self::bad('validation_failed');

        $ok = 0;
        foreach ($tagIds as $tagId) {
            $tagId = (string)$tagId;
            if ($tagId === '') continue;
            $r = $this->assign->assign($tenant, $tagId, $etype, $eid);
            $ok += ($r['ok'] ?? false) ? 1 : 0;
        }

        return self::ok(['ok' => true, 'assigned' => $ok]);
    }


    /**
     * @param string $etype
     * @return bool
     */
    private function isAllowedType(string $etype): bool
    {
        if ($etype === '') return false;
        if (in_array('*', $this->allowedTypes, true)) return true;
        return in_array($etype, $this->allowedTypes, true);
    }

    /**
     * @param string $etype
     * @return string
     */
    private static function normalizeEntityType(string $etype): string
    {
        $etype = strtolower(trim($etype));
        if ($etype === '') return '';
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $etype)) return '';
        return $etype;
    }

    /**
     * @param string $eid
     * @return string
     */
    private static function normalizeEntityId(string $eid): string
    {
        $eid = trim($eid);
        if ($eid === '') return '';
        if (strlen($eid) > 128) return '';
        return $eid;
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    private static function ok(array $body): array
    {
        return [200, ['Content-Type' => 'application/json'], json_encode($body)];
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    private static function bad(string $code): array
    {
        return [400, ['Content-Type' => 'application/json'], json_encode(['code' => $code])];
    }

    /** @return array{0:string,1:string} */
    private function readEntity(array $body): array
    {
        $etypeRaw = (string)($body['entityType'] ?? ($body['entity_type'] ?? ''));
        $eidRaw = (string)($body['entityId'] ?? ($body['entity_id'] ?? ''));
        $etype = self::normalizeEntityType($etypeRaw);
        $eid = self::normalizeEntityId($eidRaw);
        if ($etype === '' || $eid === '') return ['', ''];
        if (!$this->isAllowedType($etype)) return ['', ''];
        return [$etype, $eid];
    }
}
