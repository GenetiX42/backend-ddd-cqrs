<?php

declare(strict_types=1);

namespace Domain;

use Domain\Entity\Vehicle;
use Domain\ValueObject\FleetId;
use Domain\ValueObject\VehicleId;

interface VehicleRepositoryInterface
{
    public function save(Vehicle $vehicle): void;

    public function findById(VehicleId $vehicleId): ?Vehicle;

    public function findByPlateNumber(string $plateNumber): ?Vehicle;

    /**
     * Find a vehicle by fleet ID and plate number.
     * Returns the vehicle only if it belongs to the specified fleet, null otherwise.
     */
    public function findByFleetIdAndPlateNumber(FleetId $fleetId, string $plateNumber): ?Vehicle;
}

