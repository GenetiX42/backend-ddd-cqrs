<?php

declare(strict_types=1);

namespace App\Command;

use Domain\ValueObject\FleetId;
use Domain\ValueObject\Location;
use Domain\ValueObject\VehicleId;

final class ParkVehicleCommand
{
    public readonly FleetId $fleetId;
    public readonly VehicleId $vehicleId;
    public readonly Location $location;

    public function __construct(
        string $fleetIdString,
        string $vehiclePlateNumber,
        string $latString,
        string $lngString,
        ?string $altString = null
    ) {
        if (!is_numeric($latString)) {
            throw new \InvalidArgumentException('Latitude must be a valid number');
        }
        if (!is_numeric($lngString)) {
            throw new \InvalidArgumentException('Longitude must be a valid number');
        }
        if ($altString !== null && !is_numeric($altString)) {
            throw new \InvalidArgumentException('Altitude must be a valid number');
        }

        $lat = (float) $latString;
        $lng = (float) $lngString;
        $alt = $altString !== null ? (float) $altString : null;

        // The Value Objects will validate their own data
        $this->fleetId = FleetId::fromString($fleetIdString);
        $this->vehicleId = VehicleId::fromPlateNumber($vehiclePlateNumber);
        $this->location = Location::create($lat, $lng, $alt);
    }
}

