<?php

declare(strict_types=1);

namespace Tests;

use App\Tagging\Entity\Projection\Tag\TagAdminViewProjection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Tests\Integration\TagIntegrationDbTestCase;

final class TagAdminViewContractTest extends TagIntegrationDbTestCase
{
    public function testAdminViewUsesIntegerPrimaryKey(): void
    {
        $metadata = $this->entityManager()->getClassMetadata(TagAdminViewProjection::class);

        self::assertSame('tag_admin_view', $metadata->getTableName());
        self::assertSame(['id'], $metadata->getIdentifierFieldNames());
        self::assertSame('integer', $metadata->getTypeOfField('id'));
        self::assertSame(ClassMetadata::GENERATOR_TYPE_IDENTITY, $metadata->generatorType);
    }
}
