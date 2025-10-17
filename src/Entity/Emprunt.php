<?php

namespace App\Entity;

use App\Repository\EmpruntRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmpruntRepository::class)]
class Emprunt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $livre = null;

    #[ORM\Column(length: 255)]
    private ?string $utilisateur = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $dateEmprunt = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $dateRetour = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLivre(): ?string
    {
        return $this->livre;
    }

    public function setLivre(string $livre): static
    {
        $this->livre = $livre;

        return $this;
    }

    public function getUtilisateur(): ?string
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(string $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getDateEmprunt(): ?\DateTimeImmutable
    {
        return $this->dateEmprunt;
    }

    public function setDateEmprunt(\DateTimeImmutable $dateEmprunt): static
    {
        $this->dateEmprunt = $dateEmprunt;

        return $this;
    }

    public function getDateRetour(): ?\DateTimeImmutable
    {
        return $this->dateRetour;
    }

    public function setDateRetour(\DateTimeImmutable $dateRetour): static
    {
        $this->dateRetour = $dateRetour;

        return $this;
    }
}
