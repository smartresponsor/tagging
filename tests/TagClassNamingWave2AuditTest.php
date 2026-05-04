<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagClassNamingWave2AuditTest extends TestCase
{
    /**
     * Wave 2 keeps the component-scoped namespace and canonicalizes obvious
     * non-prefixed/non-suffixed class forms without flattening the component
     * into the default Symfony App namespace.
     */
    public function testCanonicalClassFormsExistAndLegacyWave2FormsAreGone(): void
    {
        $root = dirname(__DIR__);

        $renamed = [
            'src/Application/Write/Tag/UseCase/CreateTag.php' => 'src/Application/Write/Tag/UseCase/TagCreateUseCase.php',
            'src/Application/Write/Tag/UseCase/CreateTagInterface.php' => 'src/Application/Write/Tag/UseCase/TagCreateUseCaseInterface.php',
            'src/Cache/Store/Tag/SearchCache.php' => 'src/Cache/Store/Tag/TagSearchCache.php',
            'src/Http/Api/Tag/Middleware/Authorize.php' => 'src/Http/Api/Tag/Middleware/TagAuthorizeMiddleware.php',
            'src/Http/Api/Tag/Middleware/Observe.php' => 'src/Http/Api/Tag/Middleware/TagObserveMiddleware.php',
            'src/Http/Api/Tag/Responder/JsonResponder.php' => 'src/Http/Api/Tag/Responder/TagJsonResponder.php',
            'src/Infrastructure/Outbox/Tag/OutboxPublisher.php' => 'src/Infrastructure/Outbox/Tag/TagOutboxPublisher.php',
            'src/Ops/Metrics/PrometheusExporter.php' => 'src/Ops/Metrics/TagPrometheusExporter.php',
            'src/Ops/Security/NonceStore.php' => 'src/Ops/Security/TagNonceStore.php',
            'src/Service/Security/HmacV2Verifier.php' => 'src/Service/Security/TagHmacV2Verifier.php',
        ];

        foreach ($renamed as $legacy => $canonical) {
            self::assertFileDoesNotExist($root . '/' . $legacy, $legacy);
            self::assertFileExists($root . '/' . $canonical, $canonical);
        }
    }

    public function testTaggingNamespaceIsStillScoped(): void
    {
        $root = dirname(__DIR__);

        $files = [
            'src/Http/Api/Tag/TagAssignController.php',
            'src/Http/Api/Tag/Middleware/TagAuthorizeMiddleware.php',
            'src/Service/Core/Tag/TagAssignService.php',
            'src/Service/Security/TagHmacV2Verifier.php',
        ];

        foreach ($files as $file) {
            $content = file_get_contents($root . '/' . $file);
            self::assertIsString($content);
            self::assertStringContainsString('namespace App\\Tagging\\', $content, $file);
            self::assertStringNotContainsString('namespace App\\Http\\', $content, $file);
            self::assertStringNotContainsString('namespace App\\Service\\', $content, $file);
        }
    }
}
