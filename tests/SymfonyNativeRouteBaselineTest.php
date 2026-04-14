<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SymfonyNativeRouteBaselineTest extends TestCase
{
    public function testCorrectedSymfonyNativeRouteMapExists(): void
    {
        self::assertFileExists(dirname(__DIR__) . '/config/routes/tagging_native.yaml');
    }

    public function testCorrectedSymfonyNativeRouteMapDoesNotContainIndentedRouteKeys(): void
    {
        $path = dirname(__DIR__) . '/config/routes/tagging_native.yaml';
        self::assertFileExists($path);

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        self::assertIsArray($lines);

        foreach ($lines as $lineNumber => $line) {
            self::assertDoesNotMatchRegularExpression(
                '/^\s+tag_[A-Za-z0-9_]+:/',
                $line,
                sprintf('Route key on line %d must not be indented.', $lineNumber + 1),
            );
        }
    }

    public function testCorrectedSymfonyNativeRouteMapContainsExpectedPublicRoutes(): void
    {
        $content = file_get_contents(dirname(__DIR__) . '/config/routes/tagging_native.yaml');
        self::assertIsString($content);

        foreach ([
            'tag_create:',
            'tag_read:',
            'tag_patch:',
            'tag_delete:',
            'tag_assign:',
            'tag_unassign:',
            'tag_assignments_bulk:',
            'tag_assignments_bulk_to_entity:',
            'tag_assignments:',
            'tag_search:',
            'tag_suggest:',
            'tag_status:',
            'tag_surface:',
        ] as $routeName) {
            self::assertStringContainsString($routeName, $content);
        }
    }
}
