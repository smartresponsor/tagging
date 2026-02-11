<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Infra\Tag\InMemoryTagRepository;
use App\Service\Tag\TagService;
use PHPUnit\Framework\TestCase;

/**
 *
 */

/**
 *
 */
final class TagCoreTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreateAndList(): void
    {
        $repo = new InMemoryTagRepository();
        $svc = new TagService($repo);
        $tenantId = 'tenant-a';
        $t = $svc->create($tenantId, 'alpha', 'Alpha');
        static::assertNotEmpty($t->id());
        $items = $svc->list($tenantId, 'alp', 10);
        static::assertCount(1, $items);
    }
}
