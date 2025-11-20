<?php

namespace App\Controller;

use App\Entity\Vitrine;
use App\Entity\Member;
use App\Repository\VitrineRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vitrines')]
final class VitrineController extends AbstractController
{
    #[Route('', name: 'vitrine_index', methods: ['GET'])]
    public function index(VitrineRepository $vitrineRepository): Response
    {
        // Admin : voit toutes les vitrines
        if ($this->isGranted('ROLE_ADMIN')) {
            $vitrines = $vitrineRepository->findAll();
            
            return $this->render('vitrine/index.html.twig', [
                'vitrines' => $vitrines,
            ]);
        }
        
        /** @var Member|null $member */
        $member = $this->getUser();
        
        // Utilisateur non connecté : on le renvoie vers le login
        if (! $member) {
            return $this->redirectToRoute('app_login');
        }
        
        // Utilisateur normal : uniquement les vitrines dont il est owner
        $vitrines = $vitrineRepository->findBy([
            'owner' => $member,
        ]);
        
        return $this->render('vitrine/index.html.twig', [
            'vitrines' => $vitrines,
        ]);
    }
    
    #[Route('/{id}', name: 'vitrine_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(ManagerRegistry $doctrine, int $id): Response
    {
        $repo = $doctrine->getRepository(Vitrine::class);
        $vitrine = $repo->find($id);
        
        if (!$vitrine) {
            throw $this->createNotFoundException('The vitrine does not exist');
        }
        
        /** @var Member|null $current */
        $current = $this->getUser();
        
        // Seul l’admin ou le propriétaire peut voir la vitrine
        if (
            ! $this->isGranted('ROLE_ADMIN') &&
            (
                ! $current ||
                $vitrine->getOwner() !== $current
                )
            ) {
                throw $this->createAccessDeniedException("You cannot access another member's vitrine.");
            }
            
            return $this->render('vitrine/show.html.twig', [
                'vitrine' => $vitrine,
            ]);
    }
}
