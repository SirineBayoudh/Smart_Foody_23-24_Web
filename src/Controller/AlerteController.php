<?php

namespace App\Controller;

use App\Entity\Alerte;
use App\Repository\AlerteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    #[Route('/update-alert-type', name: 'update_alert_type')]
    public function updateAlertType(Request $request): Response
    {
        // Récupérez les données de la requête AJAX
        $alertId = $request->request->get('id_alerte');
        $newType = $request->request->get('newType');

        // Votre logique pour mettre à jour l'état de l'alerte dans la base de données
        $entityManager = $this->getDoctrine()->getManager();
        $alert = $entityManager->getRepository(Alerte::class)->find($alertId);

        if (!$alert) {
            return new JsonResponse(['success' => false]);
        }

        // Si newType est 'Non lue' (0), mettez à jour avec 'Lue' (1), sinon mettez à jour avec 'Non lue' (0)
        $newTypeValue = ($newType === 'Non lue') ? 1 : 0;
        $alert->setIsType($newTypeValue);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
