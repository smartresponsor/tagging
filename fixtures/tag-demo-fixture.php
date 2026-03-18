<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

return [
    'tags' => [
        ['id' => '01K3TAGDEMO00000000000001', 'slug' => 'electronics', 'name' => 'Electronics', 'locale' => 'en', 'weight' => 100],
        ['id' => '01K3TAGDEMO00000000000002', 'slug' => 'kitchen', 'name' => 'Kitchen', 'locale' => 'en', 'weight' => 80],
        ['id' => '01K3TAGDEMO00000000000003', 'slug' => 'premium', 'name' => 'Premium', 'locale' => 'en', 'weight' => 60],
        ['id' => '01K3TAGDEMO00000000000004', 'slug' => 'featured', 'name' => 'Featured', 'locale' => 'en', 'weight' => 55],
        ['id' => '01K3TAGDEMO00000000000005', 'slug' => 'sale', 'name' => 'Sale', 'locale' => 'en', 'weight' => 40],
        ['id' => '01K3TAGDEMO00000000000006', 'slug' => 'new-arrival', 'name' => 'New Arrival', 'locale' => 'en', 'weight' => 35],
        ['id' => '01K3TAGDEMO00000000000007', 'slug' => 'gift-idea', 'name' => 'Gift Idea', 'locale' => 'en', 'weight' => 25],
    ],
    'links' => [
        ['entity_type' => 'product', 'entity_id' => 'demo-product-1', 'tag_id' => '01K3TAGDEMO00000000000001'],
        ['entity_type' => 'product', 'entity_id' => 'demo-product-1', 'tag_id' => '01K3TAGDEMO00000000000003'],
        ['entity_type' => 'product', 'entity_id' => 'demo-product-1', 'tag_id' => '01K3TAGDEMO00000000000004'],
        ['entity_type' => 'product', 'entity_id' => 'demo-product-2', 'tag_id' => '01K3TAGDEMO00000000000002'],
        ['entity_type' => 'product', 'entity_id' => 'demo-product-2', 'tag_id' => '01K3TAGDEMO00000000000005'],
        ['entity_type' => 'collection', 'entity_id' => 'demo-collection-1', 'tag_id' => '01K3TAGDEMO00000000000005'],
        ['entity_type' => 'product', 'entity_id' => 'demo-product-3', 'tag_id' => '01K3TAGDEMO00000000000006'],
        ['entity_type' => 'product', 'entity_id' => 'demo-product-3', 'tag_id' => '01K3TAGDEMO00000000000007'],
        ['entity_type' => 'bundle', 'entity_id' => 'demo-bundle-1', 'tag_id' => '01K3TAGDEMO00000000000007'],
    ],
];
