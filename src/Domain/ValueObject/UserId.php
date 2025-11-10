<?php

declare(strict_types=1);

namespace Domain\ValueObject;

final class UserId
{
    private function __construct(
        private readonly string $value
    ) {
    }

    public static function fromString(string $userId): self
    {
        $trimmed = trim($userId);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('User ID cannot be empty');
        }

        return new self($trimmed);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(UserId $other): bool
    {
        return $this->value === $other->value;
    }
}

