<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Tagging\Http\Api\Tag\TagAssignController;
use App\Tagging\Service\Core\TagAssignOperationInterface;
use App\Tagging\Service\Core\TagQuotaService;
use App\Tagging\Service\Core\TagPolicyService;
use App\Tagging\Service\Core\TagRepositoryInterface;
use App\Tagging\Service\Core\TagValidator;
use App\Tagging\Service\Core\TagUnassignOperationInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class AssignQuotaPolicyHardeningTest extends TestCase
{
    use RequiresSqlite;

    public function testAssignControllerUses404ForMissingTagAndSupportsHeaderCase(): void
    {
        $assign = new class implements TagAssignOperationInterface {
            public function assign(
                string $tenant,
                string $tagId,
                string $entityType,
                string $entityId,
                ?string $idemKey = null,
            ): array {
                return ['ok' => false, 'code' => 'tag_not_found'];
            }
        };

        $unassign = new class implements TagUnassignOperationInterface {
            public function unassign(
                string $tenant,
                string $tagId,
                string $entityType,
                string $entityId,
                ?string $idemKey = null,
            ): array {
                return ['ok' => true];
            }
        };
        $controller = new TagAssignController($assign, $unassign, ['entity_types' => ['file']]);

        [$status, , $body] = $controller->assign([
            'headers' => ['X-Tenant-Id' => 'tenant-a'],
            'body' => ['entityType' => 'file', 'entityId' => 'file-1'],
        ], 'tag-404');

        self::assertSame(404, $status);
        self::assertSame('tag_not_found', json_decode($body, true, 512, JSON_THROW_ON_ERROR)['code']);
    }

    public function testAssignControllerUses409ForIdempotencyConflict(): void
    {
        $assign = new class implements TagAssignOperationInterface {
            public function assign(
                string $tenant,
                string $tagId,
                string $entityType,
                string $entityId,
                ?string $idemKey = null,
            ): array {
                return ['ok' => false, 'code' => 'idempotency_conflict', 'conflict' => true];
            }
        };

        $unassign = new class implements TagUnassignOperationInterface {
            public function unassign(
                string $tenant,
                string $tagId,
                string $entityType,
                string $entityId,
                ?string $idemKey = null,
            ): array {
                return ['ok' => true];
            }
        };
        $controller = new TagAssignController($assign, $unassign, ['entity_types' => ['file']]);

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
        $service = new TagQuotaService($this->quotaEntityManager(2), ['quotas' => ['max_assignments' => 2]]);
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
        self::assertFileDoesNotExist(dirname(__DIR__) . '/src/Service/Quota/Tag/TagQuota.php');
    }

    private function quotaEntityManager(int $count): EntityManagerInterface
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $qb = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        $entityManager->method('createQueryBuilder')->willReturn($qb);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);
        $query->method('getSingleScalarResult')->willReturn((string) $count);

        return $entityManager;
    }
}
