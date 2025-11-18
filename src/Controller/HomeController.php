<?php

namespace App\Controller;

use App\Entity\Member;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();
        
        // Si un membre est connecté, on le redirige directement vers sa fiche
        if ($user instanceof Member) {
            return $this->redirectToRoute('app_member_show', [
                'id' => $user->getId(),
            ]);
        }
        
        // Sinon, page d'accueil "publique"
        return $this->render('home/index.html.twig');
    }
}
