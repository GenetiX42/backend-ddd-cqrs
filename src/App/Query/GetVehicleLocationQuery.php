<?php

declare(strict_types=1);

namespace App\Query;

use Domain\ValueObject\FleetId;
use Domain\ValueObject\VehicleId;

final class GetVehicleLocationQuery
{
    public function __construct(
        public readonly FleetId $fleetId,
        public readonly VehicleId $vehicleId
    ) {
    }
}

