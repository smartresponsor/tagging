<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
/** @var string $q */ ?>
<section class="panel">
    <form method="get" action="/admin/tag">
        <input type="text" name="q" placeholder="Search tags…" value="<?php echo htmlspecialchars($q); ?>"/>
        <button type="submit">Search</button>
    </form>
    <div class="hint">Use backend API /tag/search; configure base URL in host-minimal router.</div>
    <div id="search-results" data-q="<?php echo htmlspecialchars($q); ?>"></div>
</section>
