<?php

namespace App\Controller;

use App\Repository\ArenaRepository;   
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(ArenaRepository $arenaRepository): Response
    {
        $arenas = $arenaRepository->findBy(
            ['publie' => true],
            ['id' => 'DESC'],
            3
            );
        
        return $this->render('home/index.html.twig', [
            'arenas' => $arenas,
        ]);
    }
}
