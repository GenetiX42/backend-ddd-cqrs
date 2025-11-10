<?php

declare(strict_types=1);

namespace Infra\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'fleets')]
class FleetEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: UserEntity::class, inversedBy: 'fleets')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private UserEntity $user;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToMany(targetEntity: VehicleEntity::class, inversedBy: 'fleets', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'fleet_vehicles')]
    #[ORM\JoinColumn(name: 'fleet_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'vehicle_plate_number', referencedColumnName: 'plate_number', onDelete: 'CASCADE')]
    private \Doctrine\Common\Collections\Collection $vehicles;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->vehicles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getUser(): UserEntity
    {
        return $this->user;
    }

    public function setUser(UserEntity $user): void
    {
        $this->user = $user;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getVehicles(): \Doctrine\Common\Collections\Collection
    {
        return $this->vehicles;
    }
}

