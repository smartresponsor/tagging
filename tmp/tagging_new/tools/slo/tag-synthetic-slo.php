<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
$baseUrl = rtrim(getenv('BASE_URL') ?: 'http://127.0.0.1:8080', '/');
$start = microtime(true);
$json = @file_get_contents($baseUrl . '/tag/_status');
$elapsedMs = (microtime(true) - $start) * 1000;
if (!is_string($json) || $json === '') {
    fwrite(STDERR, "SLO check failed\n");
    exit(1);
}
printf("OK tag-synthetic-slo %.2fms\n", $elapsedMs);
