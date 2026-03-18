<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

use App\ServiceInterface\Core\Tag\TransactionRunnerInterface;

final readonly class PdoTransactionRunner implements TransactionRunnerInterface
{
    public function __construct(private \PDO $pdo)
    {
    }

    /**
     * @throws \Throwable
     */
    public function run(callable $callback): mixed
    {
        $this->pdo->beginTransaction();

        try {
            $result = $callback();
            $this->pdo->commit();

            return $result;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
