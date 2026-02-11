<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Service\Tag\Slug\{Slugifier, SlugPolicy};

$tenant = getenv('TENANT') ?: 'demo';
$dsn = getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=app';
$user = getenv('DB_USER') ?: 'app';
$pass = getenv('DB_PASS') ?: 'app';
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$cfgPath = __DIR__ . '/../../config/tag_slug.yaml';
$cfg = function_exists('yaml_parse_file') ? (yaml_parse_file($cfgPath) ?: []) : [];
$reserved = $cfg['reserved_words'] ?? [];

$slugifier = new Slugifier((bool)(($cfg['lowercase'] ?? true)), (int)($cfg['max_length'] ?? 64));
$policy = new SlugPolicy($pdo, $slugifier, $reserved, (int)($cfg['max_length'] ?? 64));

$src = $argv[1] ?? 'Demo Тег №1';
$slug = $policy->make($tenant, $src);
echo json_encode(['source' => $src, 'slug' => $slug], JSON_UNESCAPED_UNICODE) . PHP_EOL;
