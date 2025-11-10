<?php

declare(strict_types=1);

namespace Infra\Repository;

use Domain\Entity\User;
use Domain\UserRepositoryInterface;
use Domain\ValueObject\UserId;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    /** @var User[] */
    private array $users = [];

    public function findById(UserId $userId): ?User
    {
        return $this->users[$userId->toString()] ?? null;
    }

    public function create(UserId $userId): void
    {
        $user = User::create($userId);
        $this->users[$userId->toString()] = $user;
    }
}

