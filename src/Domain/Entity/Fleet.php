<?php

declare(strict_types=1);

namespace Domain\Entity;

use Domain\Exception\VehicleAlreadyRegisteredException;
use Domain\ValueObject\FleetId;
use Domain\ValueObject\UserId;
use Domain\ValueObject\VehicleId;

final class Fleet
{
    /** @var VehicleId[] */
    private array $vehicles = [];

    private function __construct(
        private readonly FleetId $id,
        private readonly UserId $userId
    ) {
    }

    public static function createForUser(FleetId $fleetId, UserId $userId): self
    {
        return new self($fleetId, $userId);
    }

    public function getId(): FleetId
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function registerVehicle(VehicleId $vehicleId): void
    {
        if ($this->hasVehicle($vehicleId)) {
            throw new VehicleAlreadyRegisteredException('Vehicle has already been registered into this fleet');
        }

        $this->vehicles[] = $vehicleId;
    }

    public function hasVehicle(VehicleId $vehicleId): bool
    {
        foreach ($this->vehicles as $registeredVehicleId) {
            if ($registeredVehicleId->equals($vehicleId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return VehicleId[]
     */
    public function getVehicles(): array
    {
        return $this->vehicles;
    }
}

