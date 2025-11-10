<?php

declare(strict_types=1);

namespace Domain\ValueObject;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class FleetId
{
    private function __construct(
        private readonly UuidInterface $value
    ) {
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4());
    }

    public static function fromString(string $value): self
    {
        try {
            return new self(Uuid::fromString($value));
        } catch (\Ramsey\Uuid\Exception\InvalidUuidStringException $e) {
            throw new \InvalidArgumentException(
                sprintf('Invalid Fleet ID format: "%s". Expected a valid UUID.', $value),
                0,
                $e
            );
        }
    }

    public function toString(): string
    {
        return $this->value->toString();
    }

    public function equals(FleetId $other): bool
    {
        return $this->value->equals($other->value);
    }
}
