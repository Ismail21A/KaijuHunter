<?php

namespace App\Controller;

use App\Entity\Member;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $user = $this->getUser();
        if ($user instanceof Member) {
            return $this->redirectToRoute('app_member_show', [
                'id' => $user->getId(),
            ]);
        }
        
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastEmail = $authenticationUtils->getLastUsername();
        
        return $this->render('security/login.html.twig', [
            'last_email' => $lastEmail,
            'error'      => $error,
        ]);
    }
    
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
    }
}
