<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SnapshotPurityAuditTest extends TestCase
{
    public function testRepositoryRootDoesNotContainTransportArtifactsOrTransientWorkspaces(): void
    {
        $root = dirname(__DIR__);

        $patterns = [
            'MANIFEST.wave-*.json',
            'ZZ_*',
        ];

        $violations = [];

        foreach ($patterns as $pattern) {
            $matches = glob($root.DIRECTORY_SEPARATOR.$pattern, GLOB_NOSORT) ?: [];
            foreach ($matches as $match) {
                $violations[] = basename($match);
            }
        }

        $forbiddenDirectories = [
            'tag_cons_patched',
            'tag_fix',
            'tmp',
        ];

        foreach ($forbiddenDirectories as $directory) {
            if (is_dir($root.DIRECTORY_SEPARATOR.$directory)) {
                $violations[] = $directory.'/';
            }
        }

        sort($violations);
        $violations = array_values(array_unique($violations));

        self::assertSame(
            [],
            $violations,
            'Repository root contains transport artifacts or transient workspaces: '.implode(', ', $violations),
        );
    }
}
