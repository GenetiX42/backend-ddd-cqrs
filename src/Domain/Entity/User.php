<?php

declare(strict_types=1);

namespace Domain\Entity;

use Domain\ValueObject\UserId;

final class User
{
    private function __construct(
        private readonly UserId $id
    ) {
    }

    public static function create(UserId $id): self
    {
        return new self($id);
    }

    public function getId(): UserId
    {
        return $this->id;
    }
}

