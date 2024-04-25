<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends AbstractController
{
    #[Route('/accueil', name: 'accueil')]
    public function index(SessionInterface $session, ManagerRegistry $manager, UtilisateurRepository $repo): Response
    {

        $userId = $session->get('utilisateur')['idUtilisateur'];
        
        dump($session);

        $user = $repo->find($userId);

        dump($user);
        $role = $user->getRole();

        dump($role);
        
        return $this->render('accueil/index.html.twig', [
            'controller_name' => 'AccueilController',
            'userId' => $userId,
            'role' => $role
        ]);
    }

    
}
