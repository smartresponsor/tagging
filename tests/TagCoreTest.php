<?php
declare(strict_types=1);
namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Infra\Tag\InMemoryTagRepository;
use App\Service\Tag\TagService;

final class TagCoreTest extends TestCase {
    public function testCreateAndList(): void {
        $repo = new InMemoryTagRepository();
        $svc = new TagService($repo);
        $t = $svc->create('alpha','Alpha');
        $this->assertNotEmpty($t->id());
        $items = $svc->list('alp', 10, 0);
        $this->assertCount(1, $items);
    }
}
