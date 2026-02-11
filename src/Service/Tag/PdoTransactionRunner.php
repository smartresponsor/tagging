<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Tag;

use App\ServiceInterface\Tag\TransactionRunnerInterface;
use PDO;
use Throwable;

/**
 *
 */

/**
 *
 */
final readonly class PdoTransactionRunner implements TransactionRunnerInterface
{
    /**
     * @param \PDO $pdo
     */
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @param callable $callback
     * @return mixed
     * @throws \Throwable
     */
    public function run(callable $callback): mixed
    {
        $this->pdo->beginTransaction();

        try {
            $result = $callback();
            $this->pdo->commit();
            return $result;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
