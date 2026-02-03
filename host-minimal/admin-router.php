<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';

use App\Http\Tag\Admin\AdminController;

$base = getenv('TAG_BASE_URL') ?: 'http://localhost:8080';
$ctl = new AdminController($base);
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($path === '/admin/tag' || $path === '/admin/tag/') {
  [$code,$hdr,$body] = $ctl->index($_REQUEST);
} elseif (preg_match('#^/admin/tag/show/([^/]+)$#', $path, $m)) {
  [$code,$hdr,$body] = $ctl->show($_REQUEST, $m[1]);
} elseif (preg_match('#^/admin/tag/assign/([^/]+)$#', $path, $m)) {
  [$code,$hdr,$body] = $ctl->assign($_REQUEST, $m[1]);
} else {
  $code = 404; $hdr=['Content-Type'=>'text/plain']; $body='Not found';
}
http_response_code($code); foreach ($hdr as $k=>$v){ header($k.': '.$v); } echo $body;