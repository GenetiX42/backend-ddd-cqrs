<?php

declare(strict_types=1);

namespace Infra\Repository;

use Domain\Entity\Vehicle;
use Domain\FleetRepositoryInterface;
use Domain\ValueObject\FleetId;
use Domain\ValueObject\VehicleId;
use Domain\VehicleRepositoryInterface;

final class InMemoryVehicleRepository implements VehicleRepositoryInterface
{
    /** @var Vehicle[] */
    private array $vehicles = [];

    public function __construct(
        private readonly FleetRepositoryInterface $fleetRepository
    ) {
    }

    public function save(Vehicle $vehicle): void
    {
        $this->vehicles[$vehicle->getId()->getPlateNumber()] = $vehicle;
    }

    public function findById(VehicleId $vehicleId): ?Vehicle
    {
        return $this->vehicles[$vehicleId->getPlateNumber()] ?? null;
    }

    public function findByPlateNumber(string $plateNumber): ?Vehicle
    {
        return $this->vehicles[$plateNumber] ?? null;
    }

    public function findByFleetIdAndPlateNumber(FleetId $fleetId, string $plateNumber): ?Vehicle
    {
        $fleet = $this->fleetRepository->findById($fleetId);
        if ($fleet === null) {
            return null;
        }

        $vehicle = $this->findByPlateNumber($plateNumber);
        if ($vehicle === null) {
            return null;
        }

        // Return vehicle only if it belongs to the specified fleet
        if (!$fleet->hasVehicle($vehicle->getId())) {
            return null;
        }

        return $vehicle;
    }
}

