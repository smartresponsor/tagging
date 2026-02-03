<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
/** @var string $content */ ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Tag Admin</title>
  <link rel="stylesheet" href="/admin/tag/app.css"/>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<header>
  <h1>Tag Admin</h1>
  <nav><a href="/admin/tag">Home</a></nav>
  <div id="metrics">Loading metrics…</div>
</header>
<main>
  <?php echo $content; ?>
</main>
<script src="/admin/tag/app.js"></script>
</body>
</html>
