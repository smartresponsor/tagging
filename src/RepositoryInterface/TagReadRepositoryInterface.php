<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\RepositoryInterface;

use App\Tagging\Entity\Tag\TagEntity;
use App\Tagging\Entity\Tag\TagAssignmentEntity;
use App\Tagging\Entity\Tag\TagRelationEntity;
use App\Tagging\Entity\Tag\TagSchemeEntity;
use App\Tagging\Entity\Tag\TagSynonymEntity;

interface TagReadRepositoryInterface
{
    public function getById(string $vendorId, string $id): ?TagEntity;

    public function getBySlug(string $vendorId, string $slug): ?TagEntity;

    /** @return Tag[] */
    public function search(string $vendorId, ?string $query, int $limit, int $offset): array;

    /** @return TagAssignment[] */
    public function listAssignments(
        string $vendorId,
        string $tagId,
        ?string $type = null,
        ?string $assignedId = null,
    ): array;

    /** @return TagSynonym[] */
    public function listSynonyms(string $vendorId, string $tagId): array;

    /** @return TagRelation[] */
    public function listRelations(string $vendorId, string $tagId, ?string $type = null): array;

    public function getSchemeByName(string $vendorId, string $nameEntity): ?TagSchemeEntity;

    /** @return Tag[] */
    public function listAllTags(string $vendorId): array;

    public function countTags(string $vendorId): int;

    public function countAssignments(string $vendorId): int;

    /** @return array<int, array{tagId:string, slug:string, label:string, cnt:int}> */
    public function facetTop(string $vendorId, string $assignedType, int $limit): array;

    /** @return array<int, array{tagId:string, slug:string, label:string, cnt:int}> */
    public function tagCloud(string $vendorId, int $limit): array;

    /** @return array<int, array{key:string,value:string}> */
    public function listClassifications(string $vendorId, string $scope, string $refId): array;

    /** @return array<int, array{assigned_type:string,assigned_id:string}> */
    public function listAssignmentsByTag(string $vendorId, string $tagId): array;

    /** @return array<int, array{tag_id:string}> */
    public function listTagsByScheme(string $vendorId, string $schemeName): array;
}
