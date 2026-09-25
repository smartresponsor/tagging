<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Tagging\Service\Http\Tag\TagAssignmentAssignService;
use App\Tagging\Service\Core\TagAssignOperationInterface;
use App\Tagging\Service\Core\TagQuotaService;
use App\Tagging\Service\Core\TagPolicyManagementService;
use App\Tagging\Service\Core\TagRepositoryInterface;
use App\Tagging\Service\Core\TagValidator;
use App\Tagging\Service\Core\TagUnassignOperationInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class TagAssignQuotaPolicyHardeningTest extends TestCase
{
    use TagRequiresSqlite;

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
        $service = new TagAssignmentAssignService($assign);
        $request = Request::create(
            '/tag/assignment/assign',
            'POST',
            server: ['HTTP_X_TENANT_ID' => 'tenant-a', 'CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'tagId' => 'tag-404',
                'entityType' => 'file',
                'entityId' => 'file-1',
            ], JSON_THROW_ON_ERROR),
        );
        $response = $service($request);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('tag_not_found', json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR)['code']);
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
        $service = new TagAssignmentAssignService($assign);
        $request = Request::create(
            '/tag/assignment/assign',
            'POST',
            server: ['HTTP_X_TENANT_ID' => 'tenant-a', 'CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'tagId' => 'tag-1',
                'entityType' => 'file',
                'entityId' => 'file-1',
            ], JSON_THROW_ON_ERROR),
        );
        $response = $service($request);

        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(409, $response->getStatusCode());
        self::assertSame('idempotency_conflict', $payload['code']);
        self::assertTrue($payload['conflict']);
    }

    public function testQuotaServiceProvidesRemainingAndThrowsOnExceeded(): void
    {
        $repository = $this->createMock(TagRepositoryInterface::class);
        $repository->method('countAssignments')->with('tenant-a')->willReturn(2);
        $service = new TagQuotaService($repository, ['quotas' => ['max_assignments' => 2]]);
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
        $policy = new TagPolicyManagementService($validator, [
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
