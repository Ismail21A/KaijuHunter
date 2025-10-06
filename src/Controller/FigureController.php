<?php

namespace App\Controller;

use App\Entity\Figure;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class FigureController extends AbstractController
{
    // Consultation d’une figure (détail)
    #[Route('/figure/{id}', name: 'app_figure_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Figure $figure): Response
    {
        // ParamConverter: charge automatiquement l'entité Figure depuis l’ID
        // Si l’ID n’existe pas → Symfony renvoie une 404
        return $this->render('figure/show.html.twig', [
            'figure' => $figure,
        ]);
    }
}
