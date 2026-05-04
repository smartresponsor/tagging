<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';
$phpSdk = file_get_contents($root . '/sdk/php/tag/Client.php') ?: '';
$tsSdk = file_get_contents($root . '/sdk/ts/tag/client.ts') ?: '';
$sdkReadme = file_get_contents($root . '/sdk/README.md') ?: '';
$errors = [];
$required = [
    '/tag/search',
    '/tag/suggest',
    '/tag/_status',
    '/tag/_surface',
    '/tag/assignments/bulk',
    '/tag/assignments/bulk-to-entity',
];
foreach ($required as $needle) {
    if (!str_contains($phpSdk, $needle) || !str_contains($tsSdk, $needle)) {
        $errors[] = 'sdk missing endpoint ' . $needle;
    }
}
$requiredSymbols = ['bulkAssignments', 'assignBulkToEntity'];
foreach ($requiredSymbols as $symbol) {
    if (!str_contains($phpSdk, $symbol) || !str_contains($tsSdk, $symbol) || !str_contains($sdkReadme, $symbol . '()')) {
        $errors[] = 'sdk missing symbol ' . $symbol;
    }
}
$requiredReadmeClaims = ['authoritative `total`', 'flat payloads'];
foreach ($requiredReadmeClaims as $needle) {
    if (!str_contains($sdkReadme, $needle)) {
        $errors[] = 'sdk readme missing claim ' . $needle;
    }
}
$forbidden = ['/tag/assign-bulk', '/tag/assignment/bulk', '/tag/redirect/', '/synonym'];
foreach ($forbidden as $needle) {
    if (str_contains($phpSdk, $needle) || str_contains($tsSdk, $needle)) {
        $errors[] = 'sdk contains forbidden endpoint ' . $needle;
    }
}
if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}
echo "tag-sdk-audit: ok\n";
