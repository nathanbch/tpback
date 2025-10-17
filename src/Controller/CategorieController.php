<?php

namespace App\Controller;

use App\Entity\Categorie;
use Doctrine\ORM\EntityManagerInterface as EM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/categories', name: 'api_categories_')]
class CategorieController extends AbstractController
{
    public function __construct(private EM $em) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $items = $this->em->getRepository(Categorie::class)->findAll();
        return $this->json(array_map(fn(Categorie $c) => $this->s($c), $items), 200);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Categorie $c): JsonResponse
    {
        return $this->json($this->s($c), 200);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $p = json_decode($request->getContent(), true) ?? [];
        foreach (['nom'] as $k) {
            if (!array_key_exists($k, $p)) return $this->json(['error'=>"Champ manquant: $k"], 400);
        }
        $c = (new Categorie())
            ->setNom((string)$p['nom'])
            ->setDescription($p['description'] ?? null);

        $this->em->persist($c);
        $this->em->flush();
        return $this->json($this->s($c), 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT','PATCH'])]
    public function update(Request $request, Categorie $c): JsonResponse
    {
        $p = json_decode($request->getContent(), true) ?? [];
        if (isset($p['nom'])) $c->setNom((string)$p['nom']);
        if (array_key_exists('description', $p)) $c->setDescription($p['description']);
        $this->em->flush();
        return $this->json($this->s($c), 200);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Categorie $c): JsonResponse
    {
        $this->em->remove($c);
        $this->em->flush();
        return $this->json(null, 204);
    }

    private function s(Categorie $c): array
    {
        return [
            'id' => $c->getId(),
            'nom' => $c->getNom(),
            'description' => $c->getDescription(),
        ];
    }
}
