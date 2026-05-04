<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core;

use App\Tagging\Entity\Core\Tag\TagIdempotencyStore as IdempotencyStoreEntity;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TagIdempotencyStore
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    /** @return array{state:string,result?:array<string,mixed>} state: fresh|duplicate */
    public function begin(string $tenant, string $key, string $op, string $checksum): array
    {
        /** @var IdempotencyStoreEntity|null $existing */
        $existing = $this->entityManager->getRepository(IdempotencyStoreEntity::class)->findOneBy([
            'tenant' => $tenant,
            'key' => $key,
        ]);

        if ($existing instanceof IdempotencyStoreEntity) {
            if ($existing->checksum() !== $checksum) {
                return ['state' => 'conflict', 'result' => ['code' => 'idempotency_conflict']];
            }

            return ['state' => 'duplicate', 'result' => $existing->resultJson() ?? []];
        }

        $record = new IdempotencyStoreEntity($tenant, $key, $op, $checksum, 'pending', []);
        $this->entityManager->persist($record);
        $this->entityManager->flush();

        return ['state' => 'fresh'];
    }

    /** @param array<string,mixed> $result */
    public function complete(string $tenant, string $key, array $result): void
    {
        /** @var IdempotencyStoreEntity|null $record */
        $record = $this->entityManager->getRepository(IdempotencyStoreEntity::class)->findOneBy([
            'tenant' => $tenant,
            'key' => $key,
        ]);
        if (!$record instanceof IdempotencyStoreEntity) {
            return;
        }

        $record->complete($result);
        $this->entityManager->flush();
    }
}
