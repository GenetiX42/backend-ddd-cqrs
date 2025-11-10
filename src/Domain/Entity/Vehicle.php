<?php

declare(strict_types=1);

namespace Domain\Entity;

use Domain\Exception\VehicleAlreadyParkedException;
use Domain\ValueObject\Location;
use Domain\ValueObject\VehicleId;

final class Vehicle
{
    private ?Location $parkedLocation = null;

    private function __construct(
        private readonly VehicleId $id
    ) {
    }

    public static function create(VehicleId $id): self
    {
        return new self($id);
    }

    public function getId(): VehicleId
    {
        return $this->id;
    }

    public function park(Location $location): void
    {
        if ($this->parkedLocation !== null && $this->parkedLocation->equals($location)) {
            throw new VehicleAlreadyParkedException('Vehicle is already parked at this location');
        }

        $this->parkedLocation = $location;
    }

    public function getParkedLocation(): ?Location
    {
        return $this->parkedLocation;
    }

    public function isParkedAt(Location $location): bool
    {
        return $this->parkedLocation !== null && $this->parkedLocation->equals($location);
    }
}

