<?php

declare(strict_types=1);

namespace Infra\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class UserEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $id;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(targetEntity: FleetEntity::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $fleets;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->fleets = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getFleets(): \Doctrine\Common\Collections\Collection
    {
        return $this->fleets;
    }
}

