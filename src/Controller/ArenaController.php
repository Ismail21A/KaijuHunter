<?php

namespace App\Controller;

use App\Entity\Arena;
use App\Entity\Figure;
use App\Entity\Member;
use App\Form\ArenaType;
use App\Repository\ArenaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/arena')]
final class ArenaController extends AbstractController
{
    /**
     * Liste "publique" des Arenas (publie = true)
     */
    #[Route(name: 'app_arena_index', methods: ['GET'])]
    public function index(ArenaRepository $arenaRepository): Response
    {
        return $this->render('arena/index.html.twig', [
            // Seules les arenas publiées sont visibles ici
            'arenas' => $arenaRepository->findBy(['publie' => true]),
        ]);
    }
    
    /**
     * Création d'une Arena pour un membre donné
     * Route appelée depuis la page du membre : /arena/new/{id}
     */
    #[Route('/new/{id}', name: 'app_arena_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        Member $member,
        EntityManagerInterface $entityManager
        ): Response {
            $arena = new Arena();
            // On lie directement l’arena au propriétaire (Member)
            $arena->setOwner($member);
            
            $form = $this->createForm(ArenaType::class, $arena);
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($arena);
                $entityManager->flush();
                
                // Retour à la fiche du membre
                return $this->redirectToRoute(
                    'app_member_show',
                    ['id' => $member->getId()],
                    Response::HTTP_SEE_OTHER
                    );
            }
            
            return $this->render('arena/new.html.twig', [
                'arena' => $arena,
                'form'  => $form,
            ]);
    }
    
    #[Route('/{id}', name: 'app_arena_show', methods: ['GET'])]
    public function show(Arena $arena): Response
    {
        return $this->render('arena/show.html.twig', [
            'arena' => $arena,
        ]);
    }
    
    #[Route('/{id}/edit', name: 'app_arena_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Arena $arena,
        EntityManagerInterface $entityManager
        ): Response {
            $form = $this->createForm(ArenaType::class, $arena);
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();
                
                // Après édition, retour à la fiche du propriétaire
                $owner = $arena->getOwner();
                
                if ($owner !== null) {
                    return $this->redirectToRoute(
                        'app_member_show',
                        ['id' => $owner->getId()],
                        Response::HTTP_SEE_OTHER
                        );
                }
                
                // Fallback : si pas d'owner (cas bizarre), retour à la liste publique
                return $this->redirectToRoute('app_arena_index', [], Response::HTTP_SEE_OTHER);
            }
            
            return $this->render('arena/edit.html.twig', [
                'arena' => $arena,
                'form'  => $form,
            ]);
    }
    
    #[Route('/{id}', name: 'app_arena_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Arena $arena,
        EntityManagerInterface $entityManager
        ): Response {
            $owner = $arena->getOwner(); // on garde ça avant le remove
            
            if ($this->isCsrfTokenValid('delete' . $arena->getId(), $request->getPayload()->getString('_token'))) {
                $entityManager->remove($arena);
                $entityManager->flush();
            }
            
            if ($owner !== null) {
                return $this->redirectToRoute(
                    'app_member_show',
                    ['id' => $owner->getId()],
                    Response::HTTP_SEE_OTHER
                    );
            }
            
            return $this->redirectToRoute('app_arena_index', [], Response::HTTP_SEE_OTHER);
    }
    
    /**
     * Vue publique d'une Figure dans une Arena donnée
     */
    #[Route(
        '/{arena_id}/figure/{figure_id}',
        name: 'app_arena_figure_show',
        methods: ['GET'],
        requirements: ['arena_id' => '\d+', 'figure_id' => '\d+']
        )]
        public function figureShow(
            #[MapEntity(id: 'arena_id')] Arena $arena,
            #[MapEntity(id: 'figure_id')] Figure $figure
            ): Response {
                // Vérifier que la figure appartient bien à cette arena
                if (!$arena->getFigures()->contains($figure)) {
                    throw $this->createNotFoundException("Couldn't find this figure in this arena.");
                }
                
                // Si tu veux vraiment limiter aux arenas publiées, décommente ceci :
                // if (!$arena->isPublie()) {
                //     throw $this->createAccessDeniedException("You cannot access this arena.");
                    // }
                    
                    $this->addFlash(
                        'info',
                        sprintf(
                            'You are viewing %s in Arena #%d',
                            $figure->getName() ?? ('Figure #' . $figure->getId()),
                            $arena->getId()
                            )
                        );
                    
                    return $this->render('arena/figure_show.html.twig', [
                        'arena'  => $arena,
                        'figure' => $figure,
                    ]);
        }
}
