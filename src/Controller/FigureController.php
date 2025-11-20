<?php

namespace App\Controller;

use App\Entity\Figure;
use App\Entity\Vitrine;
use App\Entity\Member;
use App\Form\FigureType;
use App\Repository\FigureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/figure')]
final class FigureController extends AbstractController
{
    #[Route(name: 'app_figure_index', methods: ['GET'])]
    public function index(FigureRepository $figureRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $figures = $figureRepository->findAll();
            
            return $this->render('figure/index.html.twig', [
                'figures' => $figures,
            ]);
        }
        
        /** @var Member|null $member */
        $member = $this->getUser();
        
        if (!$member) {
            return $this->redirectToRoute('app_login');
        }
        
        $figures = $figureRepository->findMemberFigures($member);
        
        return $this->render('figure/index.html.twig', [
            'figures' => $figures,
        ]);
    }
    
    #[Route('/new/{id}', name: 'app_figure_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Vitrine $vitrine, EntityManagerInterface $entityManager): Response
    {
        /** @var Member|null $current */
        $current = $this->getUser();
        
        $hasAccess = $this->isGranted('ROLE_ADMIN');
        if (!$hasAccess && $current instanceof Member && $vitrine->getOwner() === $current) {
            $hasAccess = true;
        }
        
        if (!$hasAccess) {
            throw $this->createAccessDeniedException("You cannot add a figure to another member's vitrine.");
        }
        
        $figure = new Figure();
        $figure->setVitrine($vitrine);
        
        $form = $this->createForm(FigureType::class, $figure);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('imageFile')->getData();
            
            if ($imageFile) {
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/figures';
                
                $newFilename = uniqid('fig_', true) . '.' . $imageFile->guessExtension();
                
                try {
                    $imageFile->move($uploadsDir, $newFilename);
                    $figure->setImageName($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Upload de l’image impossible.');
                }
            }
            
            $entityManager->persist($figure);
            $entityManager->flush();
            
            return $this->redirectToRoute('vitrine_show', [
                'id' => $vitrine->getId(),
            ], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('figure/new.html.twig', [
            'figure' => $figure,
            'form'   => $form,
        ]);
    }
    
    #[Route('/{id}', name: 'app_figure_show', methods: ['GET'])]
    public function show(Figure $figure): Response
    {
        $hasAccess = false;
        
        if ($this->isGranted('ROLE_ADMIN')) {
            $hasAccess = true;
        } else {
            /** @var Member|null $current */
            $current = $this->getUser();
            $vitrine = $figure->getVitrine();
            
            if ($current instanceof Member && $vitrine && $vitrine->getOwner() === $current) {
                $hasAccess = true;
            }
        }
        
        if (!$hasAccess) {
            throw $this->createAccessDeniedException("You cannot access another member's figure.");
        }
        
        return $this->render('figure/show.html.twig', [
            'figure' => $figure,
        ]);
    }
    
    #[Route('/{id}/edit', name: 'app_figure_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Figure $figure, EntityManagerInterface $entityManager): Response
    {
        $hasAccess = false;
        
        if ($this->isGranted('ROLE_ADMIN')) {
            $hasAccess = true;
        } else {
            /** @var Member|null $current */
            $current = $this->getUser();
            $vitrine = $figure->getVitrine();
            
            if ($current instanceof Member && $vitrine && $vitrine->getOwner() === $current) {
                $hasAccess = true;
            }
        }
        
        if (!$hasAccess) {
            throw $this->createAccessDeniedException("You cannot edit another member's figure.");
        }
        
        $form = $this->createForm(FigureType::class, $figure);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('imageFile')->getData();
            
            if ($imageFile) {
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/figures';
                
                $newFilename = uniqid('fig_', true) . '.' . $imageFile->guessExtension();
                
                try {
                    $imageFile->move($uploadsDir, $newFilename);
                    $figure->setImageName($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Upload de l’image impossible.');
                }
            }
            
            $entityManager->flush();
            
            return $this->redirectToRoute('app_figure_show', [
                'id' => $figure->getId(),
            ], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('figure/edit.html.twig', [
            'figure' => $figure,
            'form'   => $form,
        ]);
    }
    
    #[Route('/{id}', name: 'app_figure_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Figure $figure,
        EntityManagerInterface $entityManager,
        FigureRepository $figureRepository
        ): Response {
            $hasAccess = false;
            
            if ($this->isGranted('ROLE_ADMIN')) {
                $hasAccess = true;
            } else {
                /** @var Member|null $current */
                $current = $this->getUser();
                $vitrine = $figure->getVitrine();
                
                if ($current instanceof Member && $vitrine && $vitrine->getOwner() === $current) {
                    $hasAccess = true;
                }
            }
            
            if (!$hasAccess) {
                throw $this->createAccessDeniedException("You cannot delete another member's figure.");
            }
            
            $vitrine = $figure->getVitrine();
            
            if ($this->isCsrfTokenValid('delete' . $figure->getId(), $request->getPayload()->getString('_token'))) {
                $figureRepository->remove($figure, true);
            }
            
            if ($vitrine) {
                return $this->redirectToRoute('vitrine_show', [
                    'id' => $vitrine->getId(),
                ], Response::HTTP_SEE_OTHER);
            }
            
            return $this->redirectToRoute('app_figure_index', [], Response::HTTP_SEE_OTHER);
    }
}
