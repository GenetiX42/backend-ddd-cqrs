<?php

declare(strict_types=1);

namespace Domain;

/**
 * Interface for transaction management.
 * Allows the application layer to manage transactions without depending on specific infrastructure.
 */
interface TransactionManagerInterface
{
    public function begin(): void;

    public function commit(): void;

    public function rollback(): void;

    public function flush(): void;
}

