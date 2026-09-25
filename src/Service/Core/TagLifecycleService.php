<?php

declare(strict_types=1);

namespace App\Tagging\Service\Core;

use App\Tagging\Entity\Tag\TagEntity;

final readonly class TagLifecycleService implements TagLifecycleServiceInterface
{
    public function __construct(private TagRepositoryInterface $repository) {}
    public function archive(string $tenant, ?string $id, ?string $slug): array
    {
        return $this->change($tenant, $id, $slug, true);
    }
    public function restore(string $tenant, ?string $id, ?string $slug): array
    {
        return $this->change($tenant, $id, $slug, false);
    }
    private function change(string $tenant, ?string $id, ?string $slug, bool $archive): array
    {
        $entity = null !== $id && '' !== $id
            ? $this->repository->getById($tenant, $id)
            : $this->repository->getBySlug($tenant, (string) $slug);
        if (!$entity instanceof TagEntity) {
            throw new \RuntimeException('not_found');
        }
        $method = $archive ? 'delete' : 'restore';
        if (!method_exists($entity, $method)) {
            throw new \LogicException('objecting_lifecycle_pack_required');
        }
        $entity->{$method}();
        $this->repository->saveTag($tenant, $entity);
        return ['id' => $entity->id(),'slug' => $entity->slug(),'archived' => $archive];
    }
}
