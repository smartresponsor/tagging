<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
/** @var string $id */ ?>
<section class="panel">
  <h2>Tag <?php echo htmlspecialchars($id); ?></h2>
  <div id="tag-card" data-id="<?php echo htmlspecialchars($id); ?>"></div>
  <button id="purge-btn" data-id="<?php echo htmlspecialchars($id); ?>">Purge caches</button>
</section>
