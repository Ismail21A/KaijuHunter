<?php

namespace App\Controller;

use App\Entity\Vitrine;
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
        $vitrines = $vitrineRepository->findAll();
        
        // Send data to Twig
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
        
        return $this->render('vitrine/show.html.twig', [
            'vitrine' => $vitrine,
        ]);
    }
}