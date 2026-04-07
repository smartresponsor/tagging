<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$contract = file_get_contents($root . '/contracts/http/tag-openapi.yaml') ?: '';
$errors = [];

$businessPaths = [
    '/tag:',
    '/tag/{id}:',
    '/tag/{id}/assign:',
    '/tag/{id}/unassign:',
    '/tag/assignments/bulk:',
    '/tag/assignments/bulk-to-entity:',
    '/tag/assignments:',
    '/tag/search:',
    '/tag/suggest:',
];

if (substr_count($contract, 'name: X-Tenant-Id') < count($businessPaths)) {
    $errors[] = 'OpenAPI does not document tenant header across the public business shell.';
}

foreach (['including `invalid_tenant` or `validation_failed`','including `invalid_tenant`','per-item results may include `tag_not_found`, `assign_failed`, or `validation_failed`','description: No content on successful delete','`X-Tag-Version`','`X-Tag-Surface-Version`','`Cache-Control: no-store`'] as $needle) {
    if (!str_contains($contract, $needle)) {
        $errors[] = 'OpenAPI is missing semantic needle: ' . $needle;
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

echo "tag-openapi-semantics-audit: ok\n";
