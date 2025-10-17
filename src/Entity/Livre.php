<?php

namespace App\Entity;

use App\Repository\LivreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LivreRepository::class)]
class Livre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $datePublication = null;

    #[ORM\Column(nullable: true)]
    private ?bool $disponible = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDatePublication(): ?\DateTimeImmutable
    {
        return $this->datePublication;
    }

    public function setDatePublication(\DateTimeImmutable $datePublication): static
    {
        $this->datePublication = $datePublication;

        return $this;
    }

    public function isDisponible(): ?bool
    {
        return $this->disponible;
    }

    public function setDisponible(?bool $disponible): static
    {
        $this->disponible = $disponible;

        return $this;
    }
}
