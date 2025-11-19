<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalleRepository::class)]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Numéro de la salle (1, 2, 3 …)
    #[ORM\Column(type: 'integer')]
    private ?int $numero_salle = null;

    // Nom de la salle (IMAX, Premium, Enfants…)
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nom = null;

    // Équipements spéciaux (Dolby Atmos, 3D…)
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $equipement = null;

    #[ORM\ManyToOne(inversedBy: 'salles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cinema $cinema = null;

    /**
     * @var Collection<int, Seance>
     */
    #[ORM\OneToMany(targetEntity: Seance::class, mappedBy: 'salle', orphanRemoval: true)]
    private Collection $seances;

    /**
     * @var Collection<int, Siege>
     */
    #[ORM\OneToMany(targetEntity: Siege::class, mappedBy: 'salle', orphanRemoval: true)]
    private Collection $sieges;

    public function __construct()
    {
        $this->seances = new ArrayCollection();
        $this->sieges = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroSalle(): ?int
    {
        return $this->numero_salle;
    }

    public function setNumeroSalle(int $numero_salle): static
    {
        $this->numero_salle = $numero_salle;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getEquipement(): ?string
    {
        return $this->equipement;
    }

    public function setEquipement(?string $equipement): static
    {
        $this->equipement = $equipement;
        return $this;
    }

    public function getCinema(): ?Cinema
    {
        return $this->cinema;
    }

    public function setCinema(?Cinema $cinema): static
    {
        $this->cinema = $cinema;
        return $this;
    }

    /**
     * @return Collection<int, Seance>
     */
    public function getSeances(): Collection
    {
        return $this->seances;
    }

    public function addSeance(Seance $seance): static
    {
        if (!$this->seances->contains($seance)) {
            $this->seances->add($seance);
            $seance->setSalle($this);
        }
        return $this;
    }

    public function removeSeance(Seance $seance): static
    {
        if ($this->seances->removeElement($seance)) {
            if ($seance->getSalle() === $this) {
                $seance->setSalle(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Siege>
     */
    public function getSieges(): Collection
    {
        return $this->sieges;
    }

    public function addSiege(Siege $siege): static
    {
        if (!$this->sieges->contains($siege)) {
            $this->sieges->add($siege);
            $siege->setSalle($this);
        }
        return $this;
    }

    public function removeSiege(Siege $siege): static
    {
        if ($this->sieges->removeElement($siege)) {
            if ($siege->getSalle() === $this) {
                $siege->setSalle(null);
            }
        }
        return $this;
    }

    // Capacité calculée automatiquement via les sièges
    public function getCapacite(): int
    {
        return $this->sieges->count();
    }
}
