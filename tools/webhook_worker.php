#!/usr/bin/env php
<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Tag webhook spool worker (E24)
 */
require_once __DIR__.'/../bootstrap.php'; // optional if you have autoload

use App\Service\Tag\Webhook\TagWebhookSender;

// Minimal DI bootstrap (replace with real config loader)
$cfgFile = __DIR__ . '/../config/tag_webhooks.yaml';
$cfg = [];
if (is_file($cfgFile)) {
  $txt = file_get_contents($cfgFile);
  // naive YAML keys parse (very simple)
  foreach (preg_split('/\r?\n/', $txt) as $line) {
    if (preg_match('/^([a-zA-Z_]+):\s*(.+)$/', trim($line), $m)) {
      $cfg[$m[1]] = trim($m[2], '"\'');
    }
  }
}
$sender = new TagWebhookSender($cfg);
$processed = $sender->runOnce(100);
echo "processed=$processed\n";
