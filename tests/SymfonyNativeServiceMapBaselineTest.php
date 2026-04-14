<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SymfonyNativeServiceMapBaselineTest extends TestCase
{
    public function testLayeredServiceMapsExist(): void
    {
        $root = dirname(__DIR__);

        foreach ([
            'config/services/infrastructure.yaml',
            'config/services/cache.yaml',
            'config/services/read_model.yaml',
            'config/services/application.yaml',
            'config/services/http.yaml',
            'config/services/ops.yaml',
            'config/services/core.yaml',
            'config/services/tagging.yaml',
        ] as $path) {
            self::assertFileExists($root . '/' . $path);
        }
    }

    public function testCoreServiceMapContainsRequiredAliases(): void
    {
        $content = $this->read('config/services/core.yaml');

        foreach ([
            'App\\Service\\Core\\Tag\\TagEntityQueryServiceInterface',
            'App\\Service\\Core\\Tag\\TagEntityRepositoryInterface',
            'App\\Service\\Core\\Tag\\TransactionRunnerInterface',
            'App\\Service\\Core\\Tag\\AssignOperationInterface',
            'App\\Service\\Core\\Tag\\UnassignOperationInterface',
        ] as $needle) {
            self::assertStringContainsString($needle, $content);
        }
    }

    public function testApplicationServiceMapContainsUseCaseAliases(): void
    {
        $content = $this->read('config/services/application.yaml');

        foreach ([
            'App\\Application\\Write\\Tag\\UseCase\\CreateTagInterface',
            'App\\Application\\Write\\Tag\\UseCase\\PatchTagInterface',
            'App\\Application\\Write\\Tag\\UseCase\\DeleteTagInterface',
        ] as $needle) {
            self::assertStringContainsString($needle, $content);
        }
    }

    public function testInfrastructureServiceMapDefinesPdoFactory(): void
    {
        $content = $this->read('config/services/infrastructure.yaml');

        self::assertStringContainsString('PDO:', $content);
        self::assertStringContainsString('PdoConnectionFactory', $content);
        self::assertStringContainsString('createFromEnvironment', $content);
    }

    public function testReadModelServiceMapContainsReadModelAlias(): void
    {
        $content = $this->read('config/services/read_model.yaml');

        self::assertStringContainsString('App\\Service\\Core\\Tag\\TagReadModelInterface', $content);
        self::assertStringContainsString('App\\Infrastructure\\ReadModel\\Tag\\TagReadModel', $content);
    }

    public function testOpsServiceMapContainsRuntimeConfigAndWebhookServices(): void
    {
        $content = $this->read('config/services/ops.yaml');

        foreach ([
            'TagRuntimeConfigFactory',
            'tag.webhook_config',
            'tag.observability_config',
            'tag.security_config',
            'TagWebhookRegistry',
            'TagWebhookSender',
            'TagAuditEmitter',
        ] as $needle) {
            self::assertStringContainsString($needle, $content);
        }
    }

    private function read(string $relativePath): string
    {
        $content = file_get_contents(dirname(__DIR__) . '/' . $relativePath);
        self::assertIsString($content);

        return $content;
    }
}
