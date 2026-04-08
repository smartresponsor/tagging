<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class RepoHygieneAuditTest extends TestCase
{
    public function testRepositoryDoesNotCarryWaveTransportArtifactsInRoot(): void
    {
        $forbidden = [
            'MANIFEST.wave-02.json',
            'MANIFEST.wave-03.json',
            'MANIFEST.wave-04.json',
            'ZZ_CHANGED_FILES.txt',
            'ZZ_MOVE_MAP.txt',
            'ZZ_NEXT.txt',
            'ZZ_REMOVED_FILES.txt',
            'ZZ_REMOVE_EMPTY_DIRS.txt',
            'ZZ_WAVE.txt',
        ];

        foreach ($forbidden as $relativePath) {
            self::assertFileDoesNotExist(__DIR__.'/../'.$relativePath);
        }
    }

    public function testRepositoryDoesNotCarryTransportWorkspaceDirectoriesInRoot(): void
    {
        $forbidden = [
            'tag_cons_patched',
            'tag_fix',
            'tmp',
        ];

        foreach ($forbidden as $relativePath) {
            self::assertDirectoryDoesNotExist(__DIR__.'/../'.$relativePath);
        }
    }

    public function testOpsRepoHygieneDocumentExists(): void
    {
        self::assertFileExists(__DIR__.'/../docs/ops/repo-hygiene.md');
    }
}
