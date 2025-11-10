<?php

declare(strict_types=1);

namespace App\Handler;

use App\Exception\FleetNotFoundException;
use App\Exception\VehicleNotFoundException;
use App\Exception\VehicleNotInFleetException;
use App\Query\GetVehicleLocationQuery;
use Domain\FleetRepositoryInterface;
use Domain\ValueObject\Location;
use Domain\VehicleRepositoryInterface;

final class GetVehicleLocationHandler
{
    public function __construct(
        private readonly FleetRepositoryInterface $fleetRepository,
        private readonly VehicleRepositoryInterface $vehicleRepository
    ) {
    }

    public function handle(GetVehicleLocationQuery $query): ?Location
    {
        $fleet = $this->fleetRepository->findById($query->fleetId);
        if ($fleet === null) {
            throw new FleetNotFoundException('Fleet not found');
        }

        if (!$fleet->hasVehicle($query->vehicleId)) {
            throw new VehicleNotInFleetException('Vehicle is not registered in this fleet');
        }

        $vehicle = $this->vehicleRepository->findByPlateNumber($query->vehicleId->getPlateNumber());
        if ($vehicle === null) {
            throw new VehicleNotFoundException('Vehicle not found');
        }

        return $vehicle->getParkedLocation();
    }
}

