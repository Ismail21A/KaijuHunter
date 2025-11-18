<?php

namespace App\Controller;

use App\Entity\Figure;
use App\Entity\Vitrine;
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
        return $this->render('figure/index.html.twig', [
            'figures' => $figureRepository->findAll(),
        ]);
    }
    
    #[Route('/new/{id}', name: 'app_figure_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Vitrine $vitrine, EntityManagerInterface $entityManager): Response
    {
        $figure = new Figure();
        // Contextualisation : la figure appartient à cette vitrine
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
                    // on garde exactement la même logique que chez toi
                    $figure->setImageName($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Upload de l’image impossible.');
                }
            }
            
            $entityManager->persist($figure);
            $entityManager->flush();
            
            // Retour vers la vitrine concernée
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
        return $this->render('figure/show.html.twig', [
            'figure' => $figure,
        ]);
    }
    
    #[Route('/{id}/edit', name: 'app_figure_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Figure $figure, EntityManagerInterface $entityManager): Response
    {
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
            
            // Retour vers la vitrine de cette figure si elle existe
            $vitrine = $figure->getVitrine();
            if ($vitrine) {
                return $this->redirectToRoute('vitrine_show', [
                    'id' => $vitrine->getId(),
                ], Response::HTTP_SEE_OTHER);
            }
            
            // Fallback (au cas où, mais normalement la vitrine est non nulle)
            return $this->redirectToRoute('app_figure_index', [], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('figure/edit.html.twig', [
            'figure' => $figure,
            'form'   => $form,
        ]);
    }
    
    #[Route('/{id}', name: 'app_figure_delete', methods: ['POST'])]
    public function delete(Request $request, Figure $figure, EntityManagerInterface $entityManager): Response
    {
        // On garde la vitrine avant de supprimer
        $vitrine = $figure->getVitrine();
        
        if ($this->isCsrfTokenValid('delete' . $figure->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($figure);
            $entityManager->flush();
        }
        
        if ($vitrine) {
            return $this->redirectToRoute('vitrine_show', [
                'id' => $vitrine->getId(),
            ], Response::HTTP_SEE_OTHER);
        }
        
        return $this->redirectToRoute('app_figure_index', [], Response::HTTP_SEE_OTHER);
    }
}
