<?php

declare(strict_types=1);

namespace Infra\Transaction;

use Doctrine\ORM\EntityManagerInterface;
use Domain\TransactionManagerInterface;

/**
 * Doctrine implementation of TransactionManagerInterface.
 */
final class DoctrineTransactionManager implements TransactionManagerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function begin(): void
    {
        $this->entityManager->beginTransaction();
    }

    public function commit(): void
    {
        $this->entityManager->commit();
    }

    public function rollback(): void
    {
        $this->entityManager->rollback();
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}

