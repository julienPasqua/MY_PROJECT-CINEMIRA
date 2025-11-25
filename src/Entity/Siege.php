<?php

namespace App\Entity;

use App\Enum\TypeSiege;
use App\Repository\SiegeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: SiegeRepository::class)]
class Siege
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 5)]
    private ?string $numero_rangee = null;

    #[ORM\Column]
    private ?int $numero_place = null;

    #[ORM\Column(type: Types::STRING, enumType: TypeSiege::class)]
    private ?TypeSiege $type = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    private ?string $prix_supplement = null;

    #[ORM\ManyToOne(targetEntity: Salle::class,inversedBy: 'sieges')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Salle $salle = null;

    #[ORM\ManyToMany(targetEntity: Reservation::class, mappedBy: 'sieges')]
    private Collection $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getNumeroRangee(): ?string
    {
        return $this->numero_rangee;
    }

    public function setNumeroRangee(string $numero_rangee): static
    {
        $this->numero_rangee = $numero_rangee;

        return $this;
    }

    public function getNumeroPlace(): ?int
    {
        return $this->numero_place;
    }

    public function setNumeroPlace(int $numero_place): static
    {
        $this->numero_place = $numero_place;

        return $this;
    }

    public function getType(): ?TypeSiege
    {
        return $this->type;
    }

    public function setType(TypeSiege $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getPrixSupplement(): ?string
    {
        return $this->prix_supplement;
    }

    public function setPrixSupplement(?string $prix_supplement): static
    {
        $this->prix_supplement = $prix_supplement;

        return $this;
    }

    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    public function setSalle(?Salle $salle): static
    {
        $this->salle = $salle;

        return $this;
    }



    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->addSiege($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            $reservation->removeSiege($this);
        }

        return $this;
    }





}
