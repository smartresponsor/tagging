<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Tagging\Infrastructure\Persistence\Tag\InMemoryTagRepository;
use App\Tagging\Service\Core\Tag\TagService;
use PHPUnit\Framework\TestCase;

final class TagCoreTest extends TestCase
{
    /**
     * @throws \Random\RandomException
     */
    public function testCreateAndList(): void
    {
        $repo = new InMemoryTagRepository();
        $svc = new TagService($repo);
        $tenantId = 'tenant-a';
        $t = $svc->create($tenantId, 'alpha', 'Alpha');
        self::assertNotEmpty($t->id());
        $items = $svc->list($tenantId, 'alp', 10);
        self::assertCount(1, $items);
    }
}
