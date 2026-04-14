<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SymfonyNativeServeScriptCutoverTest extends TestCase
{
    public function testLocalTagServeUsesPublicSymfonyEntry(): void
    {
        $content = $this->read('tools/local/tag-serve.sh');

        self::assertStringContainsString('-t public public/index.php', $content);
        self::assertStringNotContainsString('host-minimal/index.php', $content);
    }

    public function testContainerHostRunUsesPublicSymfonyEntry(): void
    {
        $content = $this->read('host/run.sh');

        self::assertStringContainsString('/app/public/index.php', $content);
        self::assertStringContainsString('-t /app/public /app/public/index.php', $content);
        self::assertStringNotContainsString('/app/host-minimal', $content);
    }

    private function read(string $relativePath): string
    {
        $content = file_get_contents(dirname(__DIR__) . '/' . $relativePath);
        self::assertIsString($content);

        return $content;
    }
}
