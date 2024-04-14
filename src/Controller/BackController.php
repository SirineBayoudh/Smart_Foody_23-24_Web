<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackController extends AbstractController
{
    #[Route('/back', name: 'app_back')]
    public function index(UtilisateurRepository $repo): Response
    {

        $photo = $repo->getAdminImage();

        return $this->render('back/index.html.twig', [
            'controller_name' => 'BackController',
            'photo' => $photo
        ]);
    }
}
