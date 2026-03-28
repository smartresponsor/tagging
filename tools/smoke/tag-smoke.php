<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$base = rtrim((string) (getenv('BASE_URL') ?: 'http://127.0.0.1:8080'), '/');
$tenant = (string) (getenv('TENANT') ?: 'demo');

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

/** @return array<string, mixed> */
function resultPayload(array $payload): array
{
    $result = $payload['result'] ?? null;

    return is_array($result) ? $result : $payload;
}

function expectCode(string $failureCode, int $expectedCode, int $actualCode): void
{
    if ($actualCode !== $expectedCode) {
        throw new RuntimeException($failureCode);
    }
}

$status = expectTuple('status_failed', 'GET', '/tag/_status', $tenant);
$surface = expectTuple('surface_failed', 'GET', '/tag/_surface', $tenant);
if (($surface['surface']['search'] ?? '') !== 'GET /tag/search') {
    throw new RuntimeException('surface_failed');
}

[$preflightCode] = request('OPTIONS', '/tag', $tenant);
expectCode('preflight_failed', 204, $preflightCode);

$seedSearch = expectTuple('seed_search_failed', 'GET', '/tag/search?q=elect&pageSize=10', $tenant);

$createResult = resultPayload(expectTuple(
    'create_failed',
    'POST',
    '/tag',
    $tenant,
    ['name' => 'Smoke Runtime', 'locale' => 'en', 'weight' => 7],
    ['X-Idempotency-Key: smoke-create-' . time()],
    [200, 201],
));
$tagId = (string) ($createResult['id'] ?? '');
if ($tagId === '') {
    throw new RuntimeException('create_missing_id');
}

expectTuple('get_failed', 'GET', '/tag/' . rawurlencode($tagId), $tenant);

$patchResult = resultPayload(expectTuple(
    'patch_failed',
    'PATCH',
    '/tag/' . rawurlencode($tagId),
    $tenant,
    ['name' => 'Smoke Runtime Patched', 'weight' => 9],
    ['X-Idempotency-Key: smoke-patch-' . time()],
    [200, 204],
));

$entityId = 'smoke-product-' . time();
$assignPayload = ['entity_type' => 'product', 'entity_id' => $entityId];
$assignIdem = 'smoke-assign-' . time();
expectTuple('assign_failed', 'POST', '/tag/' . rawurlencode($tagId) . '/assign', $tenant, $assignPayload, ['X-Idempotency-Key: ' . $assignIdem]);
$assignRepeat = expectTuple('assign_repeat_failed', 'POST', '/tag/' . rawurlencode($tagId) . '/assign', $tenant, $assignPayload, ['X-Idempotency-Key: ' . $assignIdem]);
if (!(($assignRepeat['duplicated'] ?? false) || ($assignRepeat['ok'] ?? false))) {
    throw new RuntimeException('assign_repeat_failed');
}

$search = expectTuple('search_failed', 'GET', '/tag/search?q=smoke&pageSize=10', $tenant);
$suggest = expectTuple('suggest_failed', 'GET', '/tag/suggest?q=smo&limit=10', $tenant);
$assignment = expectTuple(
    'assignment_read_failed',
    'GET',
    '/tag/assignments?entityType=product&entityId=' . rawurlencode($entityId) . '&limit=10',
    $tenant,
);

expectTuple('unassign_failed', 'POST', '/tag/' . rawurlencode($tagId) . '/unassign', $tenant, $assignPayload, ['X-Idempotency-Key: smoke-unassign-' . time()]);
expectTuple('delete_failed', 'DELETE', '/tag/' . rawurlencode($tagId), $tenant, null, [], [200, 204]);

fwrite(STDOUT, json_encode([
    'ok' => true,
    'tenant' => $tenant,
    'tag_id' => $tagId,
    'seed_items' => count($seedSearch['items'] ?? []),
    'seed_assignment_items' => count($assignment['items'] ?? []),
    'patched' => $patchResult['name'] ?? null,
    'surface_version' => $surface['version'] ?? null,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);
