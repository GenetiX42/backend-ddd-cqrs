<?php

declare(strict_types=1);

namespace Domain;

use Domain\Entity\User;
use Domain\ValueObject\UserId;

interface UserRepositoryInterface
{
    public function findById(UserId $userId): ?User;

    public function create(UserId $userId): void;
}

