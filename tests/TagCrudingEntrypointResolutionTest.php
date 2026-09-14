<?php

declare(strict_types=1);

namespace Tests;

use App\Cruding\DTO\CrudContextDTO;
use App\Cruding\Resolver\CrudServiceClassNameResolver;
use App\Tagging\Entity\Tag\TagEntity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TagCrudingEntrypointResolutionTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function entrypoints(): iterable
    {
        yield 'root index' => ['tag', 'index', 'TagIndexService'];
        yield 'root show' => ['tag', 'show', 'TagShowService'];
        yield 'root create' => ['tag', 'create', 'TagCreateService'];
        yield 'root update' => ['tag', 'update', 'TagUpdateService'];
        yield 'root delete' => ['tag', 'delete', 'TagDeleteService'];
        yield 'assignment assign' => ['tag/assignment', 'assign', 'TagAssignmentAssignService'];
        yield 'assignment unassign' => ['tag/assignment', 'unassign', 'TagAssignmentUnassignService'];
        yield 'relation create' => ['tag/relation', 'create', 'TagRelationCreateService'];
        yield 'synonym index' => ['tag/synonym', 'index', 'TagSynonymIndexService'];
        yield 'proposal approve' => ['tag/proposal', 'approve', 'TagProposalApproveService'];
        yield 'classification index' => ['tag/classification', 'index', 'TagClassificationIndexService'];
        yield 'scheme show' => ['tag/scheme', 'show', 'TagSchemeShowService'];
        yield 'webhook create' => ['tag/webhook', 'create', 'TagWebhookCreateService'];
    }

    #[DataProvider('entrypoints')]
    public function testCrudingDerivesExistingTaggingEntrypoint(
        string $resourcePath,
        string $operation,
        string $expectedShortClass,
    ): void {
        $context = new CrudContextDTO(
            view: 'public',
            operation: $operation,
            resourcePath: $resourcePath,
            entityClass: TagEntity::class,
            identifierField: 'id',
            identifierValue: null,
            formTypeClass: null,
        );

        $resolver = new CrudServiceClassNameResolver();

        self::assertContains($expectedShortClass, $resolver->candidateShortClassNames($context));
        self::assertTrue(
            class_exists('App\\Tagging\\Service\\Http\\Tag\\' . $expectedShortClass),
            sprintf('Tagging entrypoint %s must exist.', $expectedShortClass),
        );
    }

    public function testCrudingUsesTaggingNamespaceRootForCanonicalEntity(): void
    {
        $context = new CrudContextDTO(
            view: 'public',
            operation: 'index',
            resourcePath: 'tag',
            entityClass: TagEntity::class,
            identifierField: 'id',
            identifierValue: null,
            formTypeClass: null,
        );

        self::assertSame(
            ['App\\Tagging\\Service\\', 'App\\Service\\'],
            (new CrudServiceClassNameResolver())->candidateServiceNamespaceRootPrefixes($context),
        );
    }
}
