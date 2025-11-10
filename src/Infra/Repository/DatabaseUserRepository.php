<?php

declare(strict_types=1);

namespace Infra\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Domain\Entity\User;
use Domain\UserRepositoryInterface;
use Domain\ValueObject\UserId;
use Infra\Entity\UserEntity;

final class DatabaseUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function findById(UserId $userId): ?User
    {
        $userEntity = $this->entityManager->getRepository(UserEntity::class)
            ->find($userId->toString());

        if ($userEntity === null) {
            return null;
        }

        return User::create(UserId::fromString($userEntity->getId()));
    }

    public function create(UserId $userId): void
    {
        $userEntity = new UserEntity();
        $userEntity->setId($userId->toString());

        $this->entityManager->persist($userEntity);
        $this->entityManager->flush();
    }
}

