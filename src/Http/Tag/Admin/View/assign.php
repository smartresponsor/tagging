<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
/** @var string $id */ ?>
<section class="panel">
    <h2>Assign/Unassign — Tag <?php echo htmlspecialchars($id); ?></h2>
    <form id="assign-form" data-id="<?php echo htmlspecialchars($id); ?>">
        <label>Entity type
            <select name="entity_type">
                <option>product</option>
                <option>category</option>
                <option>project</option>
                <option>text</option>
            </select>
        </label>
        <label>Entity id <input type="text" name="entity_id" required/></label>
        <button type="submit">Assign</button>
    </form>
    <div id="assign-result"></div>
</section>
