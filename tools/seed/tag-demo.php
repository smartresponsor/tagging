<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

putenv('SEED_RESET=' . (getenv('SEED_RESET') ?: '1'));
require __DIR__ . '/tag-seed.php';
