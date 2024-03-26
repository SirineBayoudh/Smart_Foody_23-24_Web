<?php

namespace App\Controller;

use App\Repository\AlerteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AlerteController extends AbstractController
{
    #[Route('/alerte', name: 'alerte_get')]
    public function getAlerte(AlerteRepository $repo): Response
    {
        $list = $repo->findAll();


        return $this->render('alerte/index.html.twig', [
            'alerts' => $list,

        ]);
    }
}
