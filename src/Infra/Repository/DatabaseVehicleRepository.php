<?php

declare(strict_types=1);

namespace Infra\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Domain\Entity\Vehicle;
use Domain\FleetRepositoryInterface;
use Domain\ValueObject\FleetId;
use Domain\ValueObject\Location;
use Domain\ValueObject\VehicleId;
use Domain\VehicleRepositoryInterface;
use Infra\Entity\VehicleEntity;

final class DatabaseVehicleRepository implements VehicleRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FleetRepositoryInterface $fleetRepository
    ) {
    }

    public function save(Vehicle $vehicle): void
    {
        $vehicleEntity = $this->entityManager->getRepository(VehicleEntity::class)
            ->find($vehicle->getId()->getPlateNumber());

        if ($vehicleEntity === null) {
            $vehicleEntity = new VehicleEntity();
            $vehicleEntity->setPlateNumber($vehicle->getId()->getPlateNumber());
            $this->entityManager->persist($vehicleEntity);
        }

        $location = $vehicle->getParkedLocation();
        if ($location !== null) {
            $vehicleEntity->setParkedLatitude((string) $location->getLatitude());
            $vehicleEntity->setParkedLongitude((string) $location->getLongitude());
            $vehicleEntity->setParkedAltitude($location->getAltitude() !== null ? (string) $location->getAltitude() : null);
            $vehicleEntity->setParkedAt(new \DateTimeImmutable());
        } else {
            $vehicleEntity->setParkedLatitude(null);
            $vehicleEntity->setParkedLongitude(null);
            $vehicleEntity->setParkedAltitude(null);
            $vehicleEntity->setParkedAt(null);
        }

        $this->entityManager->flush();
    }

    public function findById(VehicleId $vehicleId): ?Vehicle
    {
        $vehicleEntity = $this->entityManager->getRepository(VehicleEntity::class)
            ->find($vehicleId->getPlateNumber());

        if ($vehicleEntity === null) {
            return null;
        }

        $vehicle = Vehicle::create($vehicleId);

        if ($vehicleEntity->getParkedLatitude() !== null && $vehicleEntity->getParkedLongitude() !== null) {
            $location = Location::create(
                (float) $vehicleEntity->getParkedLatitude(),
                (float) $vehicleEntity->getParkedLongitude(),
                $vehicleEntity->getParkedAltitude() !== null ? (float) $vehicleEntity->getParkedAltitude() : null
            );

            // Use reflection to set location directly (bypassing domain validation for loading)
            $reflection = new \ReflectionClass($vehicle);
            $locationProperty = $reflection->getProperty('parkedLocation');
            $locationProperty->setAccessible(true);
            $locationProperty->setValue($vehicle, $location);
        }

        return $vehicle;
    }

    public function findByPlateNumber(string $plateNumber): ?Vehicle
    {
        $vehicleEntity = $this->entityManager->getRepository(VehicleEntity::class)
            ->find($plateNumber);

        if ($vehicleEntity === null) {
            return null;
        }

        $vehicleId = VehicleId::fromPlateNumber($plateNumber);
        $vehicle = Vehicle::create($vehicleId);

        if ($vehicleEntity->getParkedLatitude() !== null && $vehicleEntity->getParkedLongitude() !== null) {
            $location = Location::create(
                (float) $vehicleEntity->getParkedLatitude(),
                (float) $vehicleEntity->getParkedLongitude(),
                $vehicleEntity->getParkedAltitude() !== null ? (float) $vehicleEntity->getParkedAltitude() : null
            );

            // Use reflection to set location directly (bypassing domain validation for loading)
            $reflection = new \ReflectionClass($vehicle);
            $locationProperty = $reflection->getProperty('parkedLocation');
            $locationProperty->setAccessible(true);
            $locationProperty->setValue($vehicle, $location);
        }

        return $vehicle;
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

