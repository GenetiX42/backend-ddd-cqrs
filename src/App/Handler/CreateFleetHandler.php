<?php

declare(strict_types=1);

namespace App\Handler;

use App\Command\CreateFleetCommand;
use Domain\Entity\Fleet;
use Domain\FleetRepositoryInterface;
use Domain\UserRepositoryInterface;
use Domain\ValueObject\FleetId;
use Domain\ValueObject\UserId;

final class CreateFleetHandler
{
    public function __construct(
        private readonly FleetRepositoryInterface $fleetRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function handle(CreateFleetCommand $command): FleetId
    {        
        $userId = UserId::fromString($command->userId);
        
        // 1. Find user, if null create it
        $userExists = $this->userRepository->findById($userId);
        if ($userExists === null) {
            $this->userRepository->create($userId);
        }
        
        // 2. Generate FleetId
        $fleetId = FleetId::generate();
        
        $fleet = Fleet::createForUser($fleetId, $userId);
        $this->fleetRepository->save($fleet);

        return $fleetId;
    }
}

