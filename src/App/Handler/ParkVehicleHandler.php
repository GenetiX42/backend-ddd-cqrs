<?php

declare(strict_types=1);

namespace App\Handler;

use App\Command\ParkVehicleCommand;
use App\Exception\InvalidVehicleFleetAssociationException;
use Domain\FleetRepositoryInterface;
use Domain\VehicleRepositoryInterface;

final class ParkVehicleHandler
{
    public function __construct(
        private readonly FleetRepositoryInterface $fleetRepository,
        private readonly VehicleRepositoryInterface $vehicleRepository
    ) {
    }

    public function handle(ParkVehicleCommand $command): void
    {
        $vehicle = $this->vehicleRepository->findByFleetIdAndPlateNumber(
            $command->fleetId,
            $command->vehicleId->getPlateNumber()
        );

        if ($vehicle === null) {
            // Security: don't reveal details about what's wrong (fleet doesn't exist, vehicle doesn't exist, or not associated)
            throw new InvalidVehicleFleetAssociationException();
        }

        $vehicle->park($command->location);
        $this->vehicleRepository->save($vehicle);
    }
}

