<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$base = rtrim((string) (getenv('BASE_URL') ?: 'http://127.0.0.1:8080'), '/');
$tenant = (string) (getenv('TENANT') ?: 'demo');

function uniqueToken(string $prefix): string
{
    return sprintf('%s-%s', $prefix, bin2hex(random_bytes(6)));
}

/**
 * @param array<string, mixed>|null $body
 * @param list<string> $extraHeaders
 * @return array{0:int,1:array<string,mixed>}
 */
function call(string $method, string $url, string $tenant, ?array $body = null, array $extraHeaders = []): array
{
    $headers = array_merge(['X-Tenant-Id: ' . $tenant], $extraHeaders);
    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $body !== null ? json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
    ]);
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($raw === false) {
        throw new RuntimeException($err !== '' ? $err : 'curl_failed');
    }
    $json = json_decode($raw, true);

    return [$code, is_array($json) ? $json : ['raw' => $raw]];
}

/** @return array{0:int,1:array<string,mixed>} */
function request(string $method, string $path, string $tenant, ?array $body = null, array $extraHeaders = []): array
{
    global $base;

    return call($method, $base . $path, $tenant, $body, $extraHeaders);
}

/** @param list<int> $allowedCodes @return array<string, mixed> */
function expectResponse(string $failureCode, array $allowedCodes, int $code, array $payload): array
{
    if (!in_array($code, $allowedCodes, true) || (($payload['ok'] ?? true) === false)) {
        throw new RuntimeException($failureCode);
    }

    return $payload;
}

/** @return array<string, mixed> */
function expectTuple(string $failureCode, string $method, string $path, string $tenant, ?array $body = null, array $extraHeaders = [], array $allowedCodes = [200]): array
{
    [$code, $payload] = request($method, $path, $tenant, $body, $extraHeaders);

    return expectResponse($failureCode, $allowedCodes, $code, $payload);
}

function expectCode(string $failureCode, int $expectedCode, int $actualCode): void
{
    if ($actualCode !== $expectedCode) {
        throw new RuntimeException($failureCode);
    }
}

/** @param array<string,mixed> $payload */
function expectNoNestedResult(string $failureCode, array $payload): void
{
    if (array_key_exists('result', $payload)) {
        throw new RuntimeException($failureCode);
    }
}

/** @param array<string,mixed> $payload */
function expectSearchShape(string $failureCode, array $payload): void
{
    $items = $payload['items'] ?? null;
    $total = $payload['total'] ?? null;
    if (!is_array($items) || !is_int($total) || $total < count($items)) {
        throw new RuntimeException($failureCode);
    }
}

/** @return list<string> */
function publicSurfacePaths(array $surfacePayload): array
{
    $rows = $surfacePayload['public_surface'] ?? [];
    if (!is_array($rows)) {
        return [];
    }

    $paths = [];
    foreach ($rows as $row) {
        if (is_array($row) && is_string($row['path'] ?? null)) {
            $paths[] = $row['path'];
        }
    }

    return $paths;
}

$status = expectTuple('status_failed', 'GET', '/tag/_status', $tenant);
$surface = expectTuple('surface_failed', 'GET', '/tag/_surface', $tenant);
if (($surface['surface']['search'] ?? '') !== 'GET /tag/search') {
    throw new RuntimeException('surface_search_missing');
}
if (($surface['surface']['assignments_bulk'] ?? '') !== 'POST /tag/assignments/bulk') {
    throw new RuntimeException('surface_bulk_missing');
}
if (($surface['surface']['assignments_bulk_to_entity'] ?? '') !== 'POST /tag/assignments/bulk-to-entity') {
    throw new RuntimeException('surface_bulk_to_entity_missing');
}
$publicPaths = publicSurfacePaths($surface);
if (!in_array('/tag/assignments/bulk', $publicPaths, true) || !in_array('/tag/assignments/bulk-to-entity', $publicPaths, true)) {
    throw new RuntimeException('public_surface_paths_missing');
}

[$preflightCode] = request('OPTIONS', '/tag', $tenant);
expectCode('preflight_failed', 204, $preflightCode);

$seedSearch = expectTuple('seed_search_failed', 'GET', '/tag/search?q=elect&pageSize=10', $tenant);
expectNoNestedResult('seed_search_nested_result_present', $seedSearch);
expectSearchShape('seed_search_shape_failed', $seedSearch);

$createResult = expectTuple(
    'create_failed',
    'POST',
    '/tag',
    $tenant,
    ['name' => 'Smoke Runtime', 'locale' => 'en', 'weight' => 7],
    ['X-Idempotency-Key: ' . uniqueToken('smoke-create')],
    [200, 201],
);
$tagId = (string) ($createResult['id'] ?? '');
if ($tagId === '') {
    throw new RuntimeException('create_missing_id');
}

expectTuple('get_failed', 'GET', '/tag/' . rawurlencode($tagId), $tenant);

$patchResult = expectTuple(
    'patch_failed',
    'PATCH',
    '/tag/' . rawurlencode($tagId),
    $tenant,
    ['name' => 'Smoke Runtime Patched', 'weight' => 9],
    ['X-Idempotency-Key: ' . uniqueToken('smoke-patch')],
    [200, 204],
);

$entityId = uniqueToken('smoke-product');
$assignPayload = ['entityType' => 'product', 'entityId' => $entityId];
$assignIdem = uniqueToken('smoke-assign');
expectTuple('assign_failed', 'POST', '/tag/' . rawurlencode($tagId) . '/assign', $tenant, $assignPayload, ['X-Idempotency-Key: ' . $assignIdem]);
$assignRepeat = expectTuple('assign_repeat_failed', 'POST', '/tag/' . rawurlencode($tagId) . '/assign', $tenant, $assignPayload, ['X-Idempotency-Key: ' . $assignIdem]);
if (!(($assignRepeat['duplicated'] ?? false) || ($assignRepeat['ok'] ?? false))) {
    throw new RuntimeException('assign_repeat_failed');
}

$bulkEntityId = uniqueToken('smoke-bulk-product');
$bulk = expectTuple(
    'bulk_assignments_failed',
    'POST',
    '/tag/assignments/bulk',
    $tenant,
    [
        'operations' => [
            ['op' => 'assign', 'tagId' => $tagId, 'entityType' => 'product', 'entityId' => $bulkEntityId, 'idem' => uniqueToken('bulk-assign')],
            ['op' => 'unassign', 'tagId' => $tagId, 'entityType' => 'product', 'entityId' => $bulkEntityId, 'idem' => uniqueToken('bulk-unassign')],
        ],
    ],
);
if ((int) ($bulk['processed'] ?? 0) !== 2 || count($bulk['results'] ?? []) !== 2) {
    throw new RuntimeException('bulk_assignments_shape_failed');
}

$bulkToEntity = expectTuple(
    'bulk_to_entity_failed',
    'POST',
    '/tag/assignments/bulk-to-entity',
    $tenant,
    ['entityType' => 'product', 'entityId' => uniqueToken('smoke-many'), 'tagIds' => [$tagId]],
);
if ((int) ($bulkToEntity['processed'] ?? 0) !== 1 || (int) ($bulkToEntity['errors'] ?? 1) !== 0) {
    throw new RuntimeException('bulk_to_entity_shape_failed');
}

$search = expectTuple('search_failed', 'GET', '/tag/search?q=smoke&pageSize=10', $tenant);
expectNoNestedResult('search_nested_result_present', $search);
expectSearchShape('search_shape_failed', $search);

$suggest = expectTuple('suggest_failed', 'GET', '/tag/suggest?q=smo&limit=10', $tenant);
expectNoNestedResult('suggest_nested_result_present', $suggest);

$assignment = expectTuple(
    'assignment_read_failed',
    'GET',
    '/tag/assignments?entityType=product&entityId=' . rawurlencode($entityId) . '&limit=10',
    $tenant,
);

[$missingCode, $missingPayload] = request(
    'POST',
    '/tag/' . rawurlencode(str_repeat('Z', 26)) . '/unassign',
    $tenant,
    ['entityType' => 'product', 'entityId' => $entityId],
    ['X-Idempotency-Key: ' . uniqueToken('smoke-missing')],
);
expectCode('missing_tag_status_failed', 404, $missingCode);
if (($missingPayload['code'] ?? '') !== 'tag_not_found') {
    throw new RuntimeException('missing_tag_payload_failed');
}

expectTuple('unassign_failed', 'POST', '/tag/' . rawurlencode($tagId) . '/unassign', $tenant, $assignPayload, ['X-Idempotency-Key: ' . uniqueToken('smoke-unassign')]);
expectTuple('delete_failed', 'DELETE', '/tag/' . rawurlencode($tagId), $tenant, null, [], [200, 204]);

fwrite(STDOUT, json_encode([
    'ok' => true,
    'tenant' => $tenant,
    'tag_id' => $tagId,
    'seed_items' => count($seedSearch['items'] ?? []),
    'seed_search_total' => $seedSearch['total'] ?? null,
    'seed_assignment_items' => count($assignment['items'] ?? []),
    'bulk_processed' => $bulk['processed'] ?? null,
    'bulk_to_entity_processed' => $bulkToEntity['processed'] ?? null,
    'patched' => $patchResult['name'] ?? null,
    'surface_version' => $surface['version'] ?? null,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);
