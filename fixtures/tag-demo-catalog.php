<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

return [
    'tenant' => 'demo',
    'primary_tag_id' => '01K3TAGDEMO00000000000001',
    'secondary_tag_id' => '01K3TAGDEMO00000000000005',
    'missing_tag_id' => '01HMISSINGTAG0000000000000',
    'primary_query' => 'elect',
    'suggest_query' => 'pre',
    'assignment_entity_type' => 'product',
    'assignment_entity_id' => 'demo-product-1',
    'scratch_entity_type' => 'collection',
    'scratch_entity_id' => 'demo-collection-2',
    'bulk_entity_type' => 'bundle',
    'bulk_entity_id' => 'demo-bundle-2',
    'known_entity' => [
        'product' => ['demo-product-1', 'demo-product-2', 'demo-product-3'],
        'collection' => ['demo-collection-1', 'demo-collection-2'],
        'bundle' => ['demo-bundle-1', 'demo-bundle-2'],
    ],
    'write_contract' => [
        'missing_tag_unassign_code' => 'tag_not_found',
        'search_payload_is_flat' => true,
        'suggest_payload_is_flat' => true,
        'search_total_is_authoritative' => true,
    ],
    'tour' => [
        'status' => '/tag/_status',
        'surface' => '/tag/_surface',
        'read' => '/tag/01K3TAGDEMO00000000000001',
        'search' => '/tag/search?q=elect&pageSize=10',
        'suggest' => '/tag/suggest?q=pre&limit=10',
        'assignments' => '/tag/assignments?entityType=product&entityId=demo-product-1&limit=10',
        'bulk' => '/tag/assignments/bulk',
        'bulk_to_entity' => '/tag/assignments/bulk-to-entity',
        'missing_unassign' => '/tag/01HMISSINGTAG0000000000000/unassign',
    ],
];
