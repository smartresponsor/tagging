<?php

declare(strict_types=1);

namespace App\Tagging\Service\Core;

use App\Tagging\Service\Core\Record\TagEntityCreateRecord;

/**
 * CRUD application gateway for TagEntity.
 *
 * The implementation is the same canonical Doctrine repository that owns the
 * aggregate read/write/policy capabilities. The separate interface preserves
 * the existing application DTO/array boundary without a second persistence
 * adapter.
 */
interface TagCrudRepositoryInterface
{
    public function existsSlug(string $tenant, string $slug, ?string $excludeId = null): bool;

    /**
     * @return array{
     *     id: string,
     *     slug: string,
     *     nameEntity: string,
     *     locale: string,
     *     weight: int,
     *     required_flag: bool,
     *     mod_only_flag: bool,
     *     created_at: string,
     *     updated_at: string
     * }|null
     */
    public function findById(string $tenant, string $id): ?array;

    /**
     * @return array{
     *     id: string,
     *     slug: string,
     *     nameEntity: string,
     *     locale: string,
     *     weight: int,
     *     required_flag: bool,
     *     mod_only_flag: bool,
     *     created_at: string,
     *     updated_at: string
     * }
     */
    public function create(string $tenant, TagEntityCreateRecord $record): array;

    /**
     * @param array{
     *     nameEntity?: string,
     *     label?: string,
     *     slug?: string,
     *     locale?: string|null,
     *     weight?: int,
     *     required_flag?: bool,
     *     mod_only_flag?: bool
     * } $patch
     */
    public function patch(string $tenant, string $id, array $patch): void;

    public function delete(string $tenant, string $id): void;
}
