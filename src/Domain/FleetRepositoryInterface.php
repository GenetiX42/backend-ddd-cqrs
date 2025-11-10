<?php

declare(strict_types=1);

namespace Domain;

use Domain\Entity\Fleet;
use Domain\ValueObject\FleetId;

interface FleetRepositoryInterface
{
    public function save(Fleet $fleet): void;

    public function findById(FleetId $fleetId): ?Fleet;
}

