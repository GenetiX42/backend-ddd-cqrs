<?php

declare(strict_types=1);

namespace App\Handler;

use App\Command\RegisterVehicleCommand;
use App\Exception\FleetNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Domain\Entity\Vehicle;
use Domain\Exception\VehicleAlreadyRegisteredException;
use Domain\FleetRepositoryInterface;
use Domain\ValueObject\VehicleId;
use Domain\VehicleRepositoryInterface;

final class RegisterVehicleHandler
{
    public function __construct(
        private readonly FleetRepositoryInterface $fleetRepository,
        private readonly VehicleRepositoryInterface $vehicleRepository,
        private readonly ?EntityManagerInterface $entityManager = null
    ) {
    }

    public function handle(RegisterVehicleCommand $command): VehicleId
    {
        // Use transaction if EntityManager is available (Doctrine repositories)
        // Otherwise (InMemory repositories), execute directly without transaction
        if ($this->entityManager !== null) {
            $this->entityManager->beginTransaction();
            try {
                $result = $this->executeRegistration($command);
                $this->entityManager->flush();
                $this->entityManager->commit();
                return $result;
            } catch (\Throwable $e) {
                $this->entityManager->rollback();
                throw $e;
            }
        }

        return $this->executeRegistration($command);
    }

    private function executeRegistration(RegisterVehicleCommand $command): VehicleId
    {
        $fleet = $this->fleetRepository->findById($command->fleetId);
        if ($fleet === null) {
            throw new FleetNotFoundException('Fleet not found');
        }
        
        if ($fleet->hasVehicle($command->vehicleId)) {
            throw new VehicleAlreadyRegisteredException('Vehicle has already been registered into this fleet');
        }

        $vehicle = $this->vehicleRepository->findById($command->vehicleId);
        
        if ($vehicle === null) {
            $vehicle = Vehicle::create($command->vehicleId);
            $this->vehicleRepository->save($vehicle);
        }
        $fleet->registerVehicle($command->vehicleId);
        $this->fleetRepository->save($fleet);

        return $command->vehicleId;
    }
}
