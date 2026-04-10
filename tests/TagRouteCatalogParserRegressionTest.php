<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagRouteCatalogParserRegressionTest extends TestCase
{
    public function testParserIgnoresMalformedTopLevelRouteLikeLines(): void
    {
        $fixture = sys_get_temp_dir() . '/tag-route-parser-' . bin2hex(random_bytes(6)) . '.yaml';
        file_put_contents(
            $fixture,
            <<<'YAML'
service: tag
runtime: host-minimal
version: parser-regression
routes:
  - operation: status
    public: true
    method: GET
    path: /tag/_status
operation: fake-top-level-route
  - operation: malformed-second-route
    public: true
    method: GET
    path: /tag/_surface
    response_header: X-Tag-Surface-Version
YAML,
        );

        $catalog = require __DIR__ . '/../config/tag_route_catalog.php';
        require_once __DIR__ . '/../config/tag_route_catalog.php';
        $parsed = \tagRouteCatalogParse($fixture);
        @unlink($fixture);

        self::assertIsArray($catalog);
        self::assertSame('parser-regression', $parsed['version']);
        self::assertCount(1, $parsed['routes']);
        self::assertSame('status', $parsed['routes'][0]['operation'] ?? null);
        self::assertSame('/tag/_status', $parsed['routes'][0]['path'] ?? null);
    }

    public function testParserPreservesQuotedValuesAndBooleanFlags(): void
    {
        $fixture = sys_get_temp_dir() . '/tag-route-parser-' . bin2hex(random_bytes(6)) . '.yaml';
        file_put_contents(
            $fixture,
            <<<'YAML'
service: 'tag'
runtime: "host-minimal"
version: quoted-values
routes:
  - operation: discovery
    public: true
    method: GET
    path: '/tag/_surface'
    response_header: "X-Tag-Surface-Version"
  - operation: webhooks_list
    public: false
    method: GET
    path: '/tag/_webhooks'
YAML,
        );

        require_once __DIR__ . '/../config/tag_route_catalog.php';
        $parsed = \tagRouteCatalogParse($fixture);
        @unlink($fixture);

        self::assertSame('tag', $parsed['service']);
        self::assertSame('host-minimal', $parsed['runtime']);
        self::assertSame('quoted-values', $parsed['version']);
        self::assertCount(2, $parsed['routes']);
        self::assertTrue($parsed['routes'][0]['public'] ?? false);
        self::assertFalse($parsed['routes'][1]['public'] ?? true);
        self::assertSame('/tag/_surface', $parsed['routes'][0]['path'] ?? null);
        self::assertSame('X-Tag-Surface-Version', $parsed['routes'][0]['response_header'] ?? null);
    }

    public function testParserReturnsDefaultCatalogWhenFileIsMissing(): void
    {
        require_once __DIR__ . '/../config/tag_route_catalog.php';
        $parsed = \tagRouteCatalogParse('/definitely/missing/tag.yaml');

        self::assertSame('tag', $parsed['service']);
        self::assertSame('host-minimal', $parsed['runtime']);
        self::assertSame('dev', $parsed['version']);
        self::assertSame([], $parsed['routes']);
    }
}
