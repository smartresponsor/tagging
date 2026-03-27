<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$base = rtrim((string) (getenv('BASE_URL') ?: 'http://127.0.0.1:8080'), '/');
$tenant = (string) (getenv('TENANT') ?: 'demo');

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

[$statusCode, $status] = call('GET', $base . '/tag/_status', $tenant);
if ($statusCode !== 200 || !($status['ok'] ?? false)) {
    throw new RuntimeException('status_failed');
}

[$surfaceCode, $surface] = call('GET', $base . '/tag/_surface', $tenant);
if ($surfaceCode !== 200 || !($surface['ok'] ?? false) || (($surface['surface']['search'] ?? '') !== 'GET /tag/search')) {
    throw new RuntimeException('surface_failed');
}

[$preflightCode] = call('OPTIONS', $base . '/tag', $tenant);
if ($preflightCode !== 204) {
    throw new RuntimeException('preflight_failed');
}

[$seedSearchCode, $seedSearch] = call('GET', $base . '/tag/search?q=elect&pageSize=10', $tenant);
if ($seedSearchCode !== 200 || !($seedSearch['ok'] ?? false)) {
    throw new RuntimeException('seed_search_failed');
}

$createIdem = 'smoke-create-' . time();
[$createCode, $create] = call('POST', $base . '/tag', $tenant, ['name' => 'Smoke Runtime', 'locale' => 'en', 'weight' => 7], ['X-Idempotency-Key: ' . $createIdem]);
$createResult = is_array($create['result'] ?? null) ? $create['result'] : $create;
if (!in_array($createCode, [200, 201], true) || !is_array($createResult)) {
    throw new RuntimeException('create_failed');
}
$tagId = (string) ($createResult['id'] ?? '');
if ($tagId === '') {
    throw new RuntimeException('create_missing_id');
}

[$getCode] = call('GET', $base . '/tag/' . rawurlencode($tagId), $tenant);
if ($getCode !== 200) {
    throw new RuntimeException('get_failed');
}

[$patchCode, $patch] = call('PATCH', $base . '/tag/' . rawurlencode($tagId), $tenant, ['name' => 'Smoke Runtime Patched', 'weight' => 9], ['X-Idempotency-Key: smoke-patch-' . time()]);
$patchResult = is_array($patch['result'] ?? null) ? $patch['result'] : (is_array($patch) ? $patch : []);
if (!in_array($patchCode, [200, 204], true)) {
    throw new RuntimeException('patch_failed');
}

$entityId = 'smoke-product-' . time();
$assignPayload = ['entity_type' => 'product', 'entity_id' => $entityId];
$assignIdem = 'smoke-assign-' . time();
[$assignCode] = call('POST', $base . '/tag/' . rawurlencode($tagId) . '/assign', $tenant, $assignPayload, ['X-Idempotency-Key: ' . $assignIdem]);
if ($assignCode !== 200) {
    throw new RuntimeException('assign_failed');
}
[$assignRepeatCode, $assignRepeat] = call('POST', $base . '/tag/' . rawurlencode($tagId) . '/assign', $tenant, $assignPayload, ['X-Idempotency-Key: ' . $assignIdem]);
if ($assignRepeatCode !== 200 || !(($assignRepeat['duplicated'] ?? false) || ($assignRepeat['ok'] ?? false))) {
    throw new RuntimeException('assign_repeat_failed');
}

[$searchCode, $search] = call('GET', $base . '/tag/search?q=smoke&pageSize=10', $tenant);
if ($searchCode !== 200 || !($search['ok'] ?? false)) {
    throw new RuntimeException('search_failed');
}

[$suggestCode, $suggest] = call('GET', $base . '/tag/suggest?q=smo&limit=10', $tenant);
if ($suggestCode !== 200 || !($suggest['ok'] ?? false)) {
    throw new RuntimeException('suggest_failed');
}

[$assignmentCode, $assignment] = call('GET', $base . '/tag/assignments?entityType=product&entityId=' . rawurlencode($entityId) . '&limit=10', $tenant);
if ($assignmentCode !== 200 || !($assignment['ok'] ?? false)) {
    throw new RuntimeException('assignment_read_failed');
}

[$unassignCode] = call('POST', $base . '/tag/' . rawurlencode($tagId) . '/unassign', $tenant, $assignPayload, ['X-Idempotency-Key: smoke-unassign-' . time()]);
if ($unassignCode !== 200) {
    throw new RuntimeException('unassign_failed');
}

[$deleteCode] = call('DELETE', $base . '/tag/' . rawurlencode($tagId), $tenant);
if (!in_array($deleteCode, [200, 204], true)) {
    throw new RuntimeException('delete_failed');
}

fwrite(STDOUT, json_encode([
    'ok' => true,
    'tenant' => $tenant,
    'tag_id' => $tagId,
    'seed_items' => count($seedSearch['items'] ?? []),
    'seed_assignment_items' => count($assignment['items'] ?? []),
    'patched' => $patchResult['name'] ?? null,
    'surface_version' => $surface['version'] ?? null,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);
