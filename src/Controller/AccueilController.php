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


        $userInfo = $session->get('utilisateur', []);

        // Vérifie si 'idUtilisateur' existe dans le tableau $userInfo
        $userId = $userInfo['idUtilisateur'] ?? null;

        if ($userId) {
            // Trouve l'utilisateur uniquement si $userId n'est pas null
            $user = $repo->find($userId);

            // Vérifie si l'utilisateur a été trouvé et récupère le rôle
            $role = $user ? $user->getRole() : 'invité';
        } else {
            // Aucun utilisateur trouvé ou non connecté, attribuer un rôle par défaut
            $role = 'invité';
        }
        
        return $this->render('accueil/index.html.twig', [
            'controller_name' => 'AccueilController',
            'userId' => $userId,
            'role' => $role
        ]);
    }

    
}
