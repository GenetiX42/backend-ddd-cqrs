<?php

declare(strict_types=1);

namespace App\Command;

use Domain\ValueObject\FleetId;
use Domain\ValueObject\VehicleId;

final class RegisterVehicleCommand
{
    public readonly FleetId $fleetId;
    public readonly VehicleId $vehicleId;

    public function __construct(
        string $fleetIdString,
        string $vehiclePlateNumber
    ) {
        $this->fleetId = FleetId::fromString($fleetIdString);
        $this->vehicleId = VehicleId::fromPlateNumber($vehiclePlateNumber);
    }
}

