<?php

declare(strict_types=1);

namespace Domain\ValueObject;

final class FleetId
{
    private static int $counter = 0;

    private function __construct(
        private readonly string $value
    ) {
    }

    public static function generate(): self
    {
        return new self('fleet-' . (++self::$counter));
    }

    public static function fromString(string $value): self
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('Fleet ID cannot be empty');
        }

        return new self($trimmed);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(FleetId $other): bool
    {
        return $this->value === $other->value;
    }
}
