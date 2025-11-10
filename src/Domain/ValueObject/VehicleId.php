<?php

declare(strict_types=1);

namespace Domain\ValueObject;

final class VehicleId
{
    private function __construct(
        private readonly string $plateNumber
    ) {
    }

    public static function fromPlateNumber(string $plateNumber): self
    {
        $trimmed = trim($plateNumber);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('Vehicle plate number cannot be empty');
        }

        return new self($trimmed);
    }

    public function getPlateNumber(): string
    {
        return $this->plateNumber;
    }

    public function equals(VehicleId $other): bool
    {
        return $this->plateNumber === $other->plateNumber;
    }
}

