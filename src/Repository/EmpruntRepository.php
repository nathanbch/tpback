<?php

namespace App\Repository;

use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EmpruntRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Emprunt::class);
    }

    public function findActiveByLivre(Livre $livre): ?Emprunt
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.livre = :livre')
            ->andWhere('e.dateRetour IS NULL')
            ->setParameter('livre', $livre)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    public function countActiveByUtilisateur(Utilisateur $u): int
    {
        return (int)$this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.utilisateur = :u')
            ->andWhere('e.dateRetour IS NULL')
            ->setParameter('u', $u)
            ->getQuery()->getSingleScalarResult();
    }

    public function findActiveByUtilisateurSorted(Utilisateur $u): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.utilisateur = :u')
            ->andWhere('e.dateRetour IS NULL')
            ->setParameter('u', $u)
            ->orderBy('e.dateEmprunt', 'ASC')
            ->getQuery()->getResult();
    }

    public function findByAuteurBetween(\DateTimeImmutable $from, \DateTimeImmutable $to, int $auteurId): array
    {
        return $this->createQueryBuilder('e')
            ->join('e.livre', 'l')
            ->join('l.auteur', 'a')
            ->andWhere('a.id = :auteurId')
            ->andWhere('e.dateEmprunt BETWEEN :from AND :to')
            ->setParameters(['auteurId' => $auteurId, 'from' => $from, 'to' => $to])
            ->getQuery()->getResult();
    }
}
