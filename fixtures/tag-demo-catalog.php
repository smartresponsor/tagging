<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

return [
    'tenant' => 'demo',
    'primary_tag_id' => '01K3TAGDEMO00000000000001',
    'primary_query' => 'elect',
    'suggest_query' => 'pre',
    'assignment_entity_type' => 'product',
    'assignment_entity_id' => 'demo-product-1',
    'known_entity' => [
        'product' => ['demo-product-1', 'demo-product-2', 'demo-product-3'],
        'collection' => ['demo-collection-1'],
        'bundle' => ['demo-bundle-1'],
    ],
    'tour' => [
        'status' => '/tag/_status',
        'surface' => '/tag/_surface',
        'search' => '/tag/search?q=elect&pageSize=10',
        'suggest' => '/tag/suggest?q=pre&limit=10',
        'assignments' => '/tag/assignments?entityType=product&entityId=demo-product-1&limit=10',
    ],
];
