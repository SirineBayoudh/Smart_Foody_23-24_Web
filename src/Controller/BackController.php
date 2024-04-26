<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class BackController extends AbstractController
{
    #[Route('/back', name: 'app_back')]
    public function index(UtilisateurRepository $repo, SessionInterface $session): Response
    {
        $userId = $session->get('utilisateur')['idUtilisateur'];

        $user = $repo->find($userId);

        $role = $user->getRole();


        if ($role == 'Admin') {

            $photo = $repo->getAdminImage();

            return $this->render('back/index.html.twig', [
                'controller_name' => 'BackController',
                'photo' => $photo,
                'user' => $user
            ]);
        } else {
            return $this->render('accueil/introuvable.html.twig', [
                'controller_name' => 'BackController',
                
            ]);
        }
    }
}
