<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core;

use Doctrine\ORM\EntityManagerInterface;

final readonly class TagDoctrineTransactionRunner implements TagTransactionRunnerInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    /**
     * @throws \Throwable
     */
    public function run(callable $callback): mixed
    {
        $this->entityManager->beginTransaction();

        try {
            $result = $callback();
            $this->entityManager->flush();
            $this->entityManager->commit();

            return $result;
        } catch (\Throwable $e) {
            if ($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }

            throw $e;
        }
    }
}
