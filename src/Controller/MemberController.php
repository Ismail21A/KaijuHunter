<?php

namespace App\Controller;

use App\Entity\Member;
use App\Repository\MemberRepository;
use App\Repository\VitrineRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/member')]
final class MemberController extends AbstractController
{
    #[Route(name: 'app_member_index', methods: ['GET'])]
    public function index(MemberRepository $memberRepository): Response
    {
        return $this->render('member/index.html.twig', [
            'members' => $memberRepository->findAll(),
        ]);
    }
    
    #[Route('/{id}', name: 'app_member_show', methods: ['GET'])]
    public function show(Member $member, VitrineRepository $vitrineRepository): Response
    {
        if (
            ! $this->isGranted('ROLE_ADMIN')
            && $this->getUser() !== $member
            ) {
                throw $this->createAccessDeniedException("Accès non autorisé à cet espace membre.");
            }
            
            $vitrine = $vitrineRepository->findOneBy(['owner' => $member]);
            
            return $this->render('member/show.html.twig', [
                'member'  => $member,
                'vitrine' => $vitrine,
                'arenas'  => $member->getArenas(), 
            ]);
    }
}
