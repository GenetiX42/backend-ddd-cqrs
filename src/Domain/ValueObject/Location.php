<?php

declare(strict_types=1);

namespace Domain\ValueObject;

final class Location
{
    private function __construct(
        private readonly float $latitude,
        private readonly float $longitude,
        private readonly ?float $altitude = null
    ) {
    }

    public static function create(float $latitude, float $longitude, ?float $altitude = null): self
    {
        // Validate latitude range (-90 to 90)
        if ($latitude < -90.0 || $latitude > 90.0) {
            throw new \InvalidArgumentException(
                sprintf('Latitude must be between -90 and 90, got: %f', $latitude)
            );
        }

        // Validate longitude range (-180 to 180)
        if ($longitude < -180.0 || $longitude > 180.0) {
            throw new \InvalidArgumentException(
                sprintf('Longitude must be between -180 and 180, got: %f', $longitude)
            );
        }

        return new self($latitude, $longitude, $altitude);
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getAltitude(): ?float
    {
        return $this->altitude;
    }

    public function equals(Location $other): bool
    {
        return $this->latitude === $other->latitude
            && $this->longitude === $other->longitude
            && $this->altitude === $other->altitude;
    }
}

