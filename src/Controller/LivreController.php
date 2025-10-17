<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Entity\Auteur;
use App\Entity\Categorie;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManagerInterface as EM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/livres', name: 'api_livres_')]
class LivreController extends AbstractController
{
    public function __construct(private EM $em, private LivreRepository $livres) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $data = array_map(fn(Livre $l) => $this->serialize($l), $this->livres->findAll());
        return $this->json($data, 200);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Livre $livre): JsonResponse
    {
        return $this->json($this->serialize($livre), 200);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $p = json_decode($request->getContent(), true) ?? [];
        foreach (['titre','datePublication','disponible','auteurId','categorieId'] as $k) {
            if (!array_key_exists($k, $p)) return $this->json(['error'=>"Champ manquant: $k"], 400);
        }

        $auteur = $this->em->getRepository(Auteur::class)->find((int)$p['auteurId']);
        $categorie = $this->em->getRepository(Categorie::class)->find((int)$p['categorieId']);
        if (!$auteur || !$categorie) return $this->json(['error'=>'Auteur ou catégorie introuvable'], 404);

        try {
            $livre = (new Livre())
                ->setTitre((string)$p['titre'])
                ->setDatePublication(new \DateTimeImmutable($p['datePublication']))
                ->setDisponible((bool)$p['disponible'])
                ->setAuteur($auteur)
                ->setCategorie($categorie);

            $this->em->persist($livre);
            $this->em->flush();

            return $this->json($this->serialize($livre), 201);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Format invalide: '.$e->getMessage()], 400);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT','PATCH'])]
    public function update(Request $request, Livre $livre): JsonResponse
    {
        $p = json_decode($request->getContent(), true) ?? [];

        if (isset($p['titre'])) $livre->setTitre((string)$p['titre']);
        if (isset($p['datePublication'])) $livre->setDatePublication(new \DateTimeImmutable($p['datePublication']));
        if (isset($p['disponible'])) $livre->setDisponible((bool)$p['disponible']);

        if (isset($p['auteurId'])) {
            $auteur = $this->em->getRepository(Auteur::class)->find((int)$p['auteurId']);
            if (!$auteur) return $this->json(['error'=>'Auteur introuvable'], 404);
            $livre->setAuteur($auteur);
        }
        if (isset($p['categorieId'])) {
            $categorie = $this->em->getRepository(Categorie::class)->find((int)$p['categorieId']);
            if (!$categorie) return $this->json(['error'=>'Catégorie introuvable'], 404);
            $livre->setCategorie($categorie);
        }

        $this->em->flush();
        return $this->json($this->serialize($livre), 200);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Livre $livre): JsonResponse
    {
        $this->em->remove($livre);
        $this->em->flush();
        return $this->json(null, 204);
    }

    private function serialize(Livre $l): array
    {
        return [
            'id' => $l->getId(),
            'titre' => $l->getTitre(),
            'datePublication' => $l->getDatePublication()?->format('Y-m-d'),
            'disponible' => $l->isDisponible(),
            'auteur' => $l->getAuteur()?->getId(),
            'categorie' => $l->getCategorie()?->getId(),
        ];
    }
}
