<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Tagging\Application\Write\Tag\Dto\CreateTagCommand;
use App\Tagging\Application\Write\Tag\Dto\DeleteTagCommand;
use App\Tagging\Application\Write\Tag\Dto\PatchTagCommand;
use App\Tagging\Application\Write\Tag\Dto\TagError;
use App\Tagging\Application\Write\Tag\Dto\TagResult;
use App\Tagging\Application\Write\Tag\UseCase\CreateTagInterface;
use App\Tagging\Application\Write\Tag\UseCase\DeleteTagInterface;
use App\Tagging\Application\Write\Tag\UseCase\PatchTagInterface;
use App\Tagging\Http\Api\Tag\Responder\TagWriteResponder;
use App\Tagging\Http\Api\Tag\TagController;
use App\Tagging\Http\Api\Tag\TagHttpRequest;
use App\Tagging\Service\Core\Tag\TagEntityQueryServiceInterface;
use PHPUnit\Framework\TestCase;

final class AdminHttpContractCleanupTest extends TestCase
{
    public function testTagHttpRequestAcceptsBothTenantHeaderVariants(): void
    {
        self::assertSame('demo', TagHttpRequest::tenant(['headers' => ['X-Tenant-Id' => 'demo']]));
        self::assertSame('demo', TagHttpRequest::tenant(['headers' => ['x-tenant-id' => 'demo']]));
    }

    public function testTagWriteResponderBadShapeIncludesOkFalseAndNoStore(): void
    {
        $response = (new TagWriteResponder())->bad('validation_failed');
        self::assertSame(400, $response[0]);
        self::assertSame('no-store', $response[1]['Cache-Control'] ?? null);
        self::assertStringContainsString('"ok":false', $response[2]);
        self::assertStringContainsString('validation_failed', $response[2]);
    }

    public function testTagControllerGetAcceptsUppercaseTenantHeader(): void
    {
        $service = new class implements TagEntityQueryServiceInterface {
            public function get(string $tenant, string $id): ?array
            {
                return ['id' => 't1', 'slug' => 'alpha', 'name' => 'Alpha', 'locale' => 'en', 'weight' => 0];
            }
        };

        $stubCreate = new class implements CreateTagInterface {
            public function execute(CreateTagCommand $command): TagResult
            {
                return TagResult::failure(TagError::ValidationFailed);
            }
        };
        $stubPatch = new class implements PatchTagInterface {
            public function execute(PatchTagCommand $command): TagResult
            {
                return TagResult::failure(TagError::ValidationFailed);
            }
        };
        $stubDelete = new class implements DeleteTagInterface {
            public function execute(DeleteTagCommand $command): TagResult
            {
                return TagResult::failure(TagError::ValidationFailed);
            }
        };

        $controller = new TagController($service, $stubCreate, $stubPatch, $stubDelete, new TagWriteResponder());
        [$status, $headers, $body] = $controller->get(['headers' => ['X-Tenant-Id' => 'demo']], 't1');

        self::assertSame(200, $status);
        self::assertSame('no-store', $headers['Cache-Control'] ?? null);
        self::assertStringContainsString('"id":"t1"', $body);
    }

    public function testAdminShellSupportsTopLevelCreateId(): void
    {
        $js = (string) file_get_contents(dirname(__DIR__) . '/admin/app.js');
        self::assertStringContainsString('parsed && parsed.id', $js);
        self::assertStringNotContainsString('parsed.result && parsed.result.id', $js);
    }
}
