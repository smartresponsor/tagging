<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Http\Api\Tag\AssignController;
use App\Service\Core\Tag\AssignOperationInterface;
use App\Service\Core\Tag\QuotaService;
use App\Service\Core\Tag\TagPolicyService;
use App\Service\Core\Tag\TagRepositoryInterface;
use App\Service\Core\Tag\TagValidator;
use App\Service\Core\Tag\UnassignOperationInterface;
use PHPUnit\Framework\TestCase;

final class AssignQuotaPolicyHardeningTest extends TestCase
{
    public function testAssignControllerUses404ForMissingTagAndSupportsHeaderCase(): void
    {
        $assign = new class implements AssignOperationInterface {
            public function assign(string $tenant, string $tagId, string $entityType, string $entityId, ?string $idemKey = null): array
            {
                return ['ok' => false, 'code' => 'tag_not_found'];
            }
        };

        $unassign = new class implements UnassignOperationInterface {
            public function unassign(string $tenant, string $tagId, string $entityType, string $entityId, ?string $idemKey = null): array
            {
                return ['ok' => true];
            }
        };
        $controller = new AssignController($assign, $unassign, ['entity_types' => ['file']]);

        [$status, , $body] = $controller->assign([
            'headers' => ['X-Tenant-Id' => 'tenant-a'],
            'body' => ['entityType' => 'file', 'entityId' => 'file-1'],
        ], 'tag-404');

        self::assertSame(404, $status);
        self::assertSame('tag_not_found', json_decode($body, true, 512, JSON_THROW_ON_ERROR)['code']);
    }

    public function testAssignControllerUses409ForIdempotencyConflict(): void
    {
        $assign = new class implements AssignOperationInterface {
            public function assign(string $tenant, string $tagId, string $entityType, string $entityId, ?string $idemKey = null): array
            {
                return ['ok' => false, 'code' => 'idempotency_conflict', 'conflict' => true];
            }
        };

        $unassign = new class implements UnassignOperationInterface {
            public function unassign(string $tenant, string $tagId, string $entityType, string $entityId, ?string $idemKey = null): array
            {
                return ['ok' => true];
            }
        };
        $controller = new AssignController($assign, $unassign, ['entity_types' => ['file']]);

        [$status, , $body] = $controller->assign([
            'headers' => ['x-tenant-id' => 'tenant-a'],
            'body' => ['entityType' => 'file', 'entityId' => 'file-1'],
        ], 'tag-1');

        $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(409, $status);
        self::assertSame('idempotency_conflict', $payload['code']);
        self::assertTrue($payload['conflict']);
    }

    public function testQuotaServiceProvidesRemainingAndThrowsOnExceeded(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE tag_link (tenant TEXT NOT NULL)');
        $pdo->exec("INSERT INTO tag_link (tenant) VALUES ('tenant-a'), ('tenant-a')");

        $service = new QuotaService($pdo, ['quotas' => ['max_assignments' => 2]]);
        $result = $service->canAssign('tenant-a');

        self::assertFalse($result['ok']);
        self::assertSame(2, $result['used']);
        self::assertSame(2, $result['max']);
        self::assertSame(0, $result['remaining']);
        self::assertSame('quota_assignments_exceeded', $result['code']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('quota_assignments_exceeded');
        $service->assertCanAssign('tenant-a');
    }

    public function testPolicyServiceAppliesAllowedAndDeniedRules(): void
    {
        $validator = new TagValidator();
        $policy = new TagPolicyService($validator, [
            'allowed_prefixes' => ['prod-'],
            'denied_prefixes' => ['prod-bad-'],
            'allowed_regex' => ['^prod-[a-z0-9-]+$'],
            'denied_regex' => ['^prod-test-'],
            'normalize' => ['lowercase' => true],
        ]);

        $repo = $this->createMock(TagRepositoryInterface::class);
        $repo->method('existsSlug')->willReturn(false);

        $policy->validateBeforeCreate('tenant-a', $repo, 'Whatever', 'prod-good');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('slug_denied_regex');
        $policy->validateBeforeCreate('tenant-a', $repo, 'Whatever', 'prod-test-item');
    }

    public function testLegacyQuotaTreeIsRemoved(): void
    {
        self::assertFileDoesNotExist(dirname(__DIR__).'/src/Service/Quota/Tag/TagQuota.php');
    }
}
