<?php

declare(strict_types=1);

namespace Infra\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Domain\Entity\Fleet;
use Domain\FleetRepositoryInterface;
use Domain\ValueObject\FleetId;
use Domain\ValueObject\UserId;
use Domain\ValueObject\VehicleId;
use Infra\Entity\FleetEntity;
use Infra\Entity\UserEntity;
use Infra\Entity\VehicleEntity;

final class DatabaseFleetRepository implements FleetRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function save(Fleet $fleet): void
    {
        $fleetEntity = $this->entityManager->getRepository(FleetEntity::class)->find($fleet->getId()->toString());

        if ($fleetEntity === null) {
            $fleetEntity = new FleetEntity();
            $fleetEntity->setId($fleet->getId()->toString());
            
            // Get user - User must exist (created by CreateFleetHandler)
            $userEntity = $this->entityManager->getRepository(UserEntity::class)->find($fleet->getUserId()->toString());
            if ($userEntity === null) {
                throw new \RuntimeException(
                    sprintf('User with ID "%s" must exist before creating a fleet', $fleet->getUserId()->toString())
                );
            }
            $fleetEntity->setUser($userEntity);
            
            $this->entityManager->persist($fleetEntity);
        }

        // Clear existing vehicles collection
        $fleetEntity->getVehicles()->clear();

        // Add vehicles to the collection
        foreach ($fleet->getVehicles() as $vehicleId) {
            $vehicleEntity = $this->entityManager->getRepository(VehicleEntity::class)
                ->find($vehicleId->getPlateNumber());

            if ($vehicleEntity === null) {
                $vehicleEntity = new VehicleEntity();
                $vehicleEntity->setPlateNumber($vehicleId->getPlateNumber());
                $this->entityManager->persist($vehicleEntity);
            }

            $fleetEntity->getVehicles()->add($vehicleEntity);
        }

        $this->entityManager->flush();
    }

    public function findById(FleetId $fleetId): ?Fleet
    {
        $fleetEntity = $this->entityManager->getRepository(FleetEntity::class)->find($fleetId->toString());

        if ($fleetEntity === null) {
            return null;
        }

        $userId = UserId::fromString($fleetEntity->getUser()->getId());
        $fleet = Fleet::createForUser($fleetId, $userId);

        // Load vehicles using reflection to bypass domain validation
        $reflection = new \ReflectionClass($fleet);
        $vehiclesProperty = $reflection->getProperty('vehicles');
        $vehiclesProperty->setAccessible(true);

        $vehiclesArray = [];
        foreach ($fleetEntity->getVehicles() as $vehicleEntity) {
            $vehicleId = VehicleId::fromPlateNumber($vehicleEntity->getPlateNumber());
            $vehiclesArray[] = $vehicleId;
        }

        $vehiclesProperty->setValue($fleet, $vehiclesArray);

        return $fleet;
    }
}
