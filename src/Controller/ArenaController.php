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
     * Liste des Arenas :
     *  - Admin : toutes
     *  - Anonyme : uniquement publie = true
     *  - Membre : publie = true + ses arenas privées
     */
    #[Route(name: 'app_arena_index', methods: ['GET'])]
    public function index(ArenaRepository $arenaRepository): Response
    {
        /** @var Member|null $member */
        $member = $this->getUser();
        
        $isAdmin = $member instanceof Member
        && \in_array('ROLE_ADMIN', $member->getRoles(), true);
        
        if ($isAdmin) {
            $arenas = $arenaRepository->findAll();
        } else {
            $publicArenas = $arenaRepository->findBy(['publie' => true]);
            
            if (!$member) {
                $arenas = $publicArenas;
            } else {
                $privateArenas = $arenaRepository->findBy([
                    'publie' => false,
                    'owner'  => $member,
                ]);
                
                $arenas = array_merge($publicArenas, $privateArenas);
                $arenas = array_unique($arenas, \SORT_REGULAR);
            }
        }
        
        return $this->render('arena/index.html.twig', [
            'arenas' => $arenas,
        ]);
    }
    
    #[Route('/new/{id}', name: 'app_arena_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        Member $member,
        EntityManagerInterface $entityManager
        ): Response {
            /** @var Member|null $current */
            $current = $this->getUser();
            
            $hasAccess = $this->isGranted('ROLE_ADMIN');
            if (!$hasAccess && $current instanceof Member && $current->getId() === $member->getId()) {
                $hasAccess = true;
            }
            
            if (!$hasAccess) {
                throw $this->createAccessDeniedException("You cannot create an arena for another member.");
            }
            
            $arena = new Arena();
            $arena->setOwner($member);
            
            $form = $this->createForm(ArenaType::class, $arena);
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($arena);
                $entityManager->flush();
                
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
        $hasAccess = false;
        
        if ($this->isGranted('ROLE_ADMIN') || $arena->isPublie()) {
            $hasAccess = true;
        } else {
            /** @var Member|null $member */
            $member = $this->getUser();
            if ($member && $arena->getOwner() === $member) {
                $hasAccess = true;
            }
        }
        
        if (!$hasAccess) {
            throw $this->createAccessDeniedException('You cannot access this arena.');
        }
        
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
            $hasAccess = false;
            
            if ($this->isGranted('ROLE_ADMIN')) {
                $hasAccess = true;
            } else {
                /** @var Member|null $current */
                $current = $this->getUser();
                if ($current && $arena->getOwner() === $current) {
                    $hasAccess = true;
                }
            }
            
            if (!$hasAccess) {
                throw $this->createAccessDeniedException('You cannot edit this arena.');
            }
            
            $form = $this->createForm(ArenaType::class, $arena);
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();
                
                return $this->redirectToRoute(
                    'app_arena_show',
                    ['id' => $arena->getId()],
                    Response::HTTP_SEE_OTHER
                    );
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
            $hasAccess = false;
            
            if ($this->isGranted('ROLE_ADMIN')) {
                $hasAccess = true;
            } else {
                /** @var Member|null $current */
                $current = $this->getUser();
                if ($current && $arena->getOwner() === $current) {
                    $hasAccess = true;
                }
            }
            
            if (!$hasAccess) {
                throw $this->createAccessDeniedException('You cannot delete this arena.');
            }
            
            $owner = $arena->getOwner();
            
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
                if (!$arena->getFigures()->contains($figure)) {
                    throw $this->createNotFoundException("Couldn't find this figure in this arena.");
                }
                
                $hasAccess = false;
                
                if ($this->isGranted('ROLE_ADMIN') || $arena->isPublie()) {
                    $hasAccess = true;
                } else {
                    /** @var Member|null $member */
                    $member = $this->getUser();
                    if ($member && $arena->getOwner() === $member) {
                        $hasAccess = true;
                    }
                }
                
                if (!$hasAccess) {
                    throw $this->createAccessDeniedException('You cannot access this arena.');
                }
                
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
