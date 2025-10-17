<?php

namespace App\Controller;

use App\Entity\Auteur;
use Doctrine\ORM\EntityManagerInterface as EM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/auteurs', name: 'api_auteurs_')]
class AuteurController extends AbstractController
{
    public function __construct(private EM $em) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $items = $this->em->getRepository(Auteur::class)->findAll();
        return $this->json(array_map(fn(Auteur $a) => $this->s($a), $items), 200);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Auteur $auteur): JsonResponse
    {
        return $this->json($this->s($auteur), 200);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $p = json_decode($request->getContent(), true) ?? [];
        foreach (['nom','prenom'] as $k) {
            if (!array_key_exists($k, $p)) return $this->json(['error'=>"Champ manquant: $k"], 400);
        }
        $a = (new Auteur())
            ->setNom((string)$p['nom'])
            ->setPrenom((string)$p['prenom'])
            ->setBiographie($p['biographie'] ?? null)
            ->setDateNaissance(isset($p['dateNaissance']) ? new \DateTimeImmutable($p['dateNaissance']) : null);

        $this->em->persist($a);
        $this->em->flush();
        return $this->json($this->s($a), 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT','PATCH'])]
    public function update(Request $request, Auteur $a): JsonResponse
    {
        $p = json_decode($request->getContent(), true) ?? [];
        if (isset($p['nom'])) $a->setNom((string)$p['nom']);
        if (isset($p['prenom'])) $a->setPrenom((string)$p['prenom']);
        if (array_key_exists('biographie', $p)) $a->setBiographie($p['biographie']);
        if (isset($p['dateNaissance'])) $a->setDateNaissance(new \DateTimeImmutable($p['dateNaissance']));
        $this->em->flush();
        return $this->json($this->s($a), 200);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Auteur $a): JsonResponse
    {
        $this->em->remove($a);
        $this->em->flush();
        return $this->json(null, 204);
    }

    private function s(Auteur $a): array
    {
        return [
            'id' => $a->getId(),
            'nom' => $a->getNom(),
            'prenom' => $a->getPrenom(),
            'biographie' => $a->getBiographie(),
            'dateNaissance' => $a->getDateNaissance()?->format('Y-m-d'),
        ];
    }
}
