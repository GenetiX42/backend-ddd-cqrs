<?php

declare(strict_types=1);

namespace Infra\Repository;

use Domain\Entity\Fleet;
use Domain\ValueObject\FleetId;
use Domain\FleetRepositoryInterface;

final class InMemoryFleetRepository implements FleetRepositoryInterface
{
    /** @var Fleet[] */
    private array $fleets = [];

    public function save(Fleet $fleet): void
    {
        $this->fleets[$fleet->getId()->toString()] = $fleet;
    }

    public function findById(FleetId $fleetId): ?Fleet
    {
        return $this->fleets[$fleetId->toString()] ?? null;
    }
}

