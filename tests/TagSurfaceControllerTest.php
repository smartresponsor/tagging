<?php

declare(strict_types=1);

namespace Tests;

use App\Tagging\Http\Api\Tag\TagRuntimeVersion;
use App\Tagging\Http\Api\Tag\TagSurfaceController;
use PHPUnit\Framework\TestCase;

final class TagSurfaceControllerTest extends TestCase
{
    public function testSurfaceContainsMinimalRoutesAndDocs(): void
    {
        $payload = (new TagSurfaceController())->surface();
        self::assertTrue($payload['ok']);
        self::assertSame('hosted-package', $payload['runtime']);
        self::assertSame('/tag/_surface', $payload['surface']['discovery']);
        self::assertArrayHasKey('tour', $payload['examples']);
        self::assertArrayHasKey('admin', $payload['docs']);
        self::assertSame(TagRuntimeVersion::read(), $payload['version']);
    }
}
