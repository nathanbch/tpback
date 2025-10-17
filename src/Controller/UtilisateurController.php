<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface as EM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/utilisateurs', name: 'api_utilisateurs_')]
class UtilisateurController extends AbstractController
{
    public function __construct(private EM $em) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $items = $this->em->getRepository(Utilisateur::class)->findAll();
        return $this->json(array_map(fn(Utilisateur $u) => $this->s($u), $items), 200);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Utilisateur $u): JsonResponse
    {
        return $this->json($this->s($u), 200);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $p = json_decode($request->getContent(), true) ?? [];
        foreach (['nom','prenom'] as $k) {
            if (!array_key_exists($k, $p)) return $this->json(['error'=>"Champ manquant: $k"], 400);
        }
        $u = (new Utilisateur())
            ->setNom((string)$p['nom'])
            ->setPrenom((string)$p['prenom']);

        $this->em->persist($u);
        $this->em->flush();
        return $this->json($this->s($u), 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT','PATCH'])]
    public function update(Request $request, Utilisateur $u): JsonResponse
    {
        $p = json_decode($request->getContent(), true) ?? [];
        if (isset($p['nom'])) $u->setNom((string)$p['nom']);
        if (isset($p['prenom'])) $u->setPrenom((string)$p['prenom']);
        $this->em->flush();
        return $this->json($this->s($u), 200);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Utilisateur $u): JsonResponse
    {
        $this->em->remove($u);
        $this->em->flush();
        return $this->json(null, 204);
    }

    private function s(Utilisateur $u): array
    {
        return [
            'id' => $u->getId(),
            'nom' => $u->getNom(),
            'prenom' => $u->getPrenom(),
        ];
    }
}
