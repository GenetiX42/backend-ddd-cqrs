<?php

declare(strict_types=1);

namespace Infra\Transaction;

use Domain\TransactionManagerInterface;

/**
 * In-memory implementation of TransactionManagerInterface.
 * No-op implementation for in-memory repositories (no transactions needed).
 */
final class InMemoryTransactionManager implements TransactionManagerInterface
{
    public function begin(): void
    {
        // No-op for in-memory repositories
    }

    public function commit(): void
    {
        // No-op for in-memory repositories
    }

    public function rollback(): void
    {
        // No-op for in-memory repositories
    }

    public function flush(): void
    {
        // No-op for in-memory repositories
    }
}

