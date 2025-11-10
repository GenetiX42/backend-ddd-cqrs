<?php

declare(strict_types=1);

namespace Infra\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vehicles')]
class VehicleEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    private string $plateNumber;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    private ?string $parkedLatitude = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    private ?string $parkedLongitude = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $parkedAltitude = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $parkedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    /**
     * Many-to-Many relation with FleetEntity (inverse side).
     * The relation is mapped by the "vehicles" property in FleetEntity.
     */
    #[ORM\ManyToMany(targetEntity: FleetEntity::class, mappedBy: 'vehicles')]
    private \Doctrine\Common\Collections\Collection $fleets;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->fleets = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getPlateNumber(): string
    {
        return $this->plateNumber;
    }

    public function setPlateNumber(string $plateNumber): void
    {
        $this->plateNumber = $plateNumber;
    }

    public function getParkedLatitude(): ?string
    {
        return $this->parkedLatitude;
    }

    public function setParkedLatitude(?string $parkedLatitude): void
    {
        $this->parkedLatitude = $parkedLatitude;
    }

    public function getParkedLongitude(): ?string
    {
        return $this->parkedLongitude;
    }

    public function setParkedLongitude(?string $parkedLongitude): void
    {
        $this->parkedLongitude = $parkedLongitude;
    }

    public function getParkedAltitude(): ?string
    {
        return $this->parkedAltitude;
    }

    public function setParkedAltitude(?string $parkedAltitude): void
    {
        $this->parkedAltitude = $parkedAltitude;
    }

    public function getParkedAt(): ?\DateTimeImmutable
    {
        return $this->parkedAt;
    }

    public function setParkedAt(?\DateTimeImmutable $parkedAt): void
    {
        $this->parkedAt = $parkedAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection<int, FleetEntity>
     */
    public function getFleets(): \Doctrine\Common\Collections\Collection
    {
        return $this->fleets;
    }
}

