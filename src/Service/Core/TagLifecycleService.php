<?php

declare(strict_types=1);

namespace App\Tagging\Service\Core;

use App\Tagging\Entity\Tag\TagEntity;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TagLifecycleService implements TagLifecycleServiceInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}
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
        $entity = $this->entityManager->getRepository(TagEntity::class)->findOneBy(array_filter(['tenant' => $tenant,'id' => $id,'slug' => $slug], static fn($v) => null !== $v && '' !== $v));
        if (!$entity instanceof TagEntity) {
            throw new \RuntimeException('not_found');
        }
        $method = $archive ? 'delete' : 'restore';
        if (!method_exists($entity, $method)) {
            throw new \LogicException('objecting_lifecycle_pack_required');
        }
        $entity->{$method}();
        $this->entityManager->flush();
        return ['id' => $entity->id(),'slug' => $entity->slug(),'archived' => $archive];
    }
}
