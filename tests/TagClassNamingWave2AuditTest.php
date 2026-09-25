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
            'src/Application/Write/Tag/UseCase/CreateTag.php' => 'src/Handler/Write/TagCreateHandler.php',
            'src/Application/Write/Tag/UseCase/CreateTagInterface.php' => 'src/HandlerInterface/Write/TagCreateHandlerInterface.php',
            'src/Cache/Store/Tag/SearchCache.php' => 'src/Cache/Store/Tag/TagSearchCache.php',
            'src/Http/Api/Tag/Middleware/Authorize.php' => 'src/Middleware/TagAuthorizeMiddleware.php',
            'src/Http/Api/Tag/Middleware/Observe.php' => 'src/Middleware/TagObserveMiddleware.php',
            'src/Http/Api/Tag/Responder/JsonResponder.php' => 'src/Responder/Api/TagJsonResponder.php',
            'src/Infrastructure/Outbox/Tag/OutboxPublisher.php' => 'src/Repository/Outbox/TagOutboxPublisher.php',
            'src/Ops/Metrics/PrometheusExporter.php' => 'src/Reporter/Metrics/TagPrometheusExporter.php',
            'src/Ops/Security/NonceStore.php' => 'src/Cache/Security/TagNonceStore.php',
            'src/Service/Security/HmacV2Verifier.php' => 'src/Verifier/Security/TagHmacV2Verifier.php',
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
            'src/Service/Http/Tag/TagAssignmentAssignService.php',
            'src/Middleware/TagAuthorizeMiddleware.php',
            'src/Service/Core/TagAssignService.php',
            'src/Verifier/Security/TagHmacV2Verifier.php',
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
