<?php

namespace App\Controller;

use App\Entity\Figure;
use App\Repository\FigureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/figures')]
final class FigureController extends AbstractController
{
    // 🧩 Liste de toutes les figures
    #[Route('', name: 'figure_index', methods: ['GET'])]
    public function index(FigureRepository $figureRepository): Response
    {
        // Récupérer toutes les figures de la base
        $figures = $figureRepository->findAll();
        
        // Afficher via Twig
        return $this->render('figure/index.html.twig', [
            'figures' => $figures,
        ]);
    }
    
    // 🧩 Consultation d’une figure (détail)
    #[Route('/{id}', name: 'figure_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Figure $figure): Response
    {
        // ParamConverter : charge automatiquement l'entité Figure depuis l’ID
        // Si l’ID n’existe pas → Symfony renvoie une 404
        return $this->render('figure/show.html.twig', [
            'figure' => $figure,
        ]);
    }
}