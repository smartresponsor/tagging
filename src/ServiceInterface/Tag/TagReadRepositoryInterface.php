<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Tag;

use App\Domain\Tag\Tag;
use App\Domain\Tag\TagAssignment;
use App\Domain\Tag\TagRelation;
use App\Domain\Tag\TagScheme;
use App\Domain\Tag\TagSynonym;

/**
 *
 */

/**
 *
 */
interface TagReadRepositoryInterface
{
    /**
     * @param string $tenantId
     * @param string $id
     * @return \App\Domain\Tag\Tag|null
     */
    public function getById(string $tenantId, string $id): ?Tag;

    /**
     * @param string $tenantId
     * @param string $slug
     * @return \App\Domain\Tag\Tag|null
     */
    public function getBySlug(string $tenantId, string $slug): ?Tag;

    /** @return Tag[] */
    public function search(string $tenantId, ?string $query, int $limit, int $offset): array;

    /** @return TagAssignment[] */
    public function listAssignments(string $tenantId, string $tagId, ?string $type = null, ?string $assignedId = null): array;

    /** @return TagSynonym[] */
    public function listSynonyms(string $tenantId, string $tagId): array;

    /** @return TagRelation[] */
    public function listRelations(string $tenantId, string $tagId, ?string $type = null): array;

    /**
     * @param string $tenantId
     * @param string $name
     * @return \App\Domain\Tag\TagScheme|null
     */
    public function getSchemeByName(string $tenantId, string $name): ?TagScheme;

    /** @return Tag[] */
    public function listAllTags(string $tenantId): array;

    /** @return array<int, array{tagId:string, slug:string, label:string, cnt:int}> */
    public function facetTop(string $tenantId, string $assignedType, int $limit): array;

    /** @return array<int, array{tagId:string, slug:string, label:string, cnt:int}> */
    public function tagCloud(string $tenantId, int $limit): array;

    /** @return array<int, array{key:string,value:string}> */
    public function listClassifications(string $tenantId, string $scope, string $refId): array;

    /** @return array<int, array{assigned_type:string,assigned_id:string}> */
    public function listAssignmentsByTag(string $tenantId, string $tagId): array;

    /** @return array<int, array{tag_id:string}> */
    public function listTagsByScheme(string $tenantId, string $schemeName): array;
}
