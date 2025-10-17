<?php

namespace App\Controller;

use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Entity\Utilisateur;
use App\Entity\Auteur;
use Doctrine\ORM\EntityManagerInterface as EM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class EmpruntController extends AbstractController
{
    public function __construct(private EM $em) {}

    // POST /api/emprunts
    #[Route('/emprunts', name: 'emprunts_create', methods: ['POST'])]
    public function emprunter(Request $request): JsonResponse
    {
        $p = json_decode($request->getContent(), true) ?? [];
        foreach (['utilisateurId','livreId'] as $k) {
            if (!isset($p[$k])) return $this->json(['error'=>"Champ manquant: $k"], 400);
        }

        $user = $this->em->getRepository(Utilisateur::class)->find((int)$p['utilisateurId']);
        $livre = $this->em->getRepository(Livre::class)->find((int)$p['livreId']);
        if (!$user || !$livre) return $this->json(['error'=>'Utilisateur ou livre introuvable'], 404);

        // 1) Livre déjà emprunté (emprunt actif = dateRetour NULL) ?
        $empruntActif = $this->em->createQueryBuilder()
            ->select('e.id')
            ->from(Emprunt::class, 'e')
            ->where('e.livre = :livre')
            ->andWhere('e.dateRetour IS NULL')
            ->setParameter('livre', $livre)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        if ($empruntActif) {
            return $this->json(['error' => 'Livre indisponible (déjà emprunté)'], 409);
        }

        // 2) Limite de 4 emprunts actifs par utilisateur
        $nbActifs = (int)$this->em->createQueryBuilder()
            ->select('COUNT(e.id)')
            ->from(Emprunt::class, 'e')
            ->where('e.utilisateur = :u')
            ->andWhere('e.dateRetour IS NULL')
            ->setParameter('u', $user)
            ->getQuery()->getSingleScalarResult();

        if ($nbActifs >= 4) {
            return $this->json(['error' => 'Limite de 4 emprunts actifs atteinte'], 422);
        }

        // 3) Créer l’emprunt
        $emprunt = (new Emprunt())
            ->setUtilisateur($user)
            ->setLivre($livre)
            ->setDateEmprunt(new \DateTimeImmutable());

        // Marquer le livre indisponible (si tu as le bool disponible côté Livre)
        if (method_exists($livre, 'setDisponible')) {
            $livre->setDisponible(false);
        }

        $this->em->persist($emprunt);
        $this->em->flush();

        return $this->json($this->serializeEmprunt($emprunt), 201);
    }

    // PUT /api/emprunts/{id}/retour
    #[Route('/emprunts/{id}/retour', name: 'emprunts_retour', methods: ['PUT'])]
    public function rendre(Emprunt $emprunt): JsonResponse
    {
        if ($emprunt->getDateRetour() !== null) {
            return $this->json(['error' => 'Emprunt déjà clôturé'], 409);
        }

        $emprunt->setDateRetour(new \DateTimeImmutable());

        $livre = $emprunt->getLivre();
        if ($livre && method_exists($livre, 'setDisponible')) {
            $livre->setDisponible(true);
        }

        $this->em->flush();

        return $this->json($this->serializeEmprunt($emprunt), 200);
    }

    // GET /api/utilisateurs/{id}/emprunts?status=open|all (tri requis sur open)
    #[Route('/utilisateurs/{id}/emprunts', name: 'utilisateur_emprunts', methods: ['GET'])]
    public function empruntsUtilisateur(Utilisateur $utilisateur, Request $request): JsonResponse
    {
        $status = $request->query->get('status', 'open');

        $qb = $this->em->createQueryBuilder()
            ->select('e', 'l')
            ->from(Emprunt::class, 'e')
            ->leftJoin('e.livre', 'l')
            ->where('e.utilisateur = :u')
            ->setParameter('u', $utilisateur);

        if ($status === 'open') {
            $qb->andWhere('e.dateRetour IS NULL')
               ->orderBy('e.dateEmprunt', 'ASC');
        } else {
            $qb->orderBy('e.dateEmprunt', 'DESC');
        }

        $emprunts = $qb->getQuery()->getResult();

        return $this->json([
            'utilisateurId' => $utilisateur->getId(),
            'enCours' => count(array_filter($emprunts, fn(Emprunt $e) => $e->getDateRetour() === null)),
            'items' => array_map(fn(Emprunt $e) => $this->serializeEmprunt($e), $emprunts),
        ], 200);
    }

    // GET /api/auteurs/{id}/livres-empruntes?from=YYYY-MM-DD&to=YYYY-MM-DD
    #[Route('/auteurs/{id}/livres-empruntes', name: 'auteur_livres_empruntes', methods: ['GET'])]
    public function livresAuteurEntre(int $id, Request $request): JsonResponse
    {
        $from = $request->query->get('from');
        $to   = $request->query->get('to');
        if (!$from || !$to) return $this->json(['error'=>'Paramètres "from" et "to" requis (YYYY-MM-DD)'], 400);

        try {
            $fromDt = (new \DateTimeImmutable($from))->setTime(0,0,0);
            $toDt   = (new \DateTimeImmutable($to))->setTime(23,59,59);
        } catch (\Throwable) {
            return $this->json(['error'=>'Format de date invalide'], 400);
        }

        // vérif auteur existe
        $auteur = $this->em->getRepository(Auteur::class)->find($id);
        if (!$auteur) return $this->json(['error'=>'Auteur introuvable'], 404);

        // requête manuelle
        $emprunts = $this->em->createQueryBuilder()
            ->select('e', 'l')
            ->from(Emprunt::class, 'e')
            ->join('e.livre', 'l')
            ->join('l.auteur', 'a')
            ->where('a.id = :aid')
            ->andWhere('e.dateEmprunt BETWEEN :from AND :to')
            ->setParameter('aid', $id)
            ->setParameter('from', $fromDt)
            ->setParameter('to', $toDt)
            ->orderBy('e.dateEmprunt', 'ASC')
            ->getQuery()->getResult();

        $items = array_map(function (Emprunt $e) {
            $l = $e->getLivre();
            return [
                'empruntId' => $e->getId(),
                'livreId' => $l?->getId(),
                'titre' => $l?->getTitre(),
                'dateEmprunt' => $e->getDateEmprunt()?->format(\DateTimeInterface::ATOM),
                'dateRetour'  => $e->getDateRetour()?->format(\DateTimeInterface::ATOM),
            ];
        }, $emprunts);

        return $this->json([
            'auteurId' => $auteur->getId(),
            'auteurNom' => ($auteur->getPrenom() ?? '').' '.($auteur->getNom() ?? ''),
            'from' => $fromDt->format('Y-m-d'),
            'to' => $toDt->format('Y-m-d'),
            'items' => $items,
        ], 200);
    }

    private function serializeEmprunt(Emprunt $e): array
    {
        return [
            'id' => $e->getId(),
            'livre' => $e->getLivre()?->getId(),
            'utilisateur' => $e->getUtilisateur()?->getId(),
            'dateEmprunt' => $e->getDateEmprunt()?->format(\DateTimeInterface::ATOM),
            'dateRetour'  => $e->getDateRetour()?->format(\DateTimeInterface::ATOM),
        ];
    }
}
