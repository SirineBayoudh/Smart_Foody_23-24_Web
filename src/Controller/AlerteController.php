<?php

namespace App\Controller;

use App\Entity\Alerte;
use App\Repository\AlerteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
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

    // #[Route('/update-alert-type', name: 'update_alert_type')]
    // public function updateAlertType(Request $request): Response
    // {
    //     // Récupérez les données de la requête AJAX
    //     $alertId = $request->request->get('id_alerte');
    //     $newType = $request->request->get('newType');

    //     // Votre logique pour mettre à jour l'état de l'alerte dans la base de données
    //     $entityManager = $this->getDoctrine()->getManager();
    //     $alert = $entityManager->getRepository(Alerte::class)->find($alertId);

    //     if (!$alert) {
    //         return new JsonResponse(['success' => false]);
    //     }

    //     // Si newType est 'Non lue' (0), mettez à jour avec 'Lue' (1), sinon mettez à jour avec 'Non lue' (0)
    //     $newTypeValue = ($newType === 'Non lue') ? 1 : 0;
    //     $alert->setIsType($newTypeValue);
    //     $entityManager->flush();

    //     return new JsonResponse(['success' => true]);
    // }

    #[Route('/update-alert-type/{id_alerte}', name: 'update_alert_type', methods: ['GET'])]
    public function markAlertAsRead($id_alerte): Response
    {
        $alert = $this->getDoctrine()->getRepository(Alerte::class)->find($id_alerte);

        if (!$alert) {
            throw $this->createNotFoundException('Alerte non trouvée');
        }

        // Mettez à jour le type d'alerte
        $alert->setType(true); // Marquez l'alerte comme lue

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        // Redirigez vers la page des alertes
        return $this->redirectToRoute('alerte_get');
    }

    #[Route('/delete-read-alerts/{alertId}', name: 'delete_read_alerts')]
    public function deleteReadAlerts($alertId, EntityManagerInterface $entityManager, FlashBagInterface $flashBag): Response
    {
        // Récupérer l'alerte à partir de son ID
        $alert = $entityManager->getRepository(Alerte::class)->find($alertId);

        // if (!$alert) {
        //     // Si l'alerte n'est pas trouvée, afficher un message d'erreur
        //     $flashBag->add('error', 'L\'alerte sélectionnée n\'existe pas.');
        //     return $this->redirectToRoute('alerte_get');
        // }

        // Date limite pour la suppression des alertes lues après 2 jours
        $limitDate = new \DateTime('-2 days');

        // Vérifier si l'alerte est lue et si la date de l'alerte est antérieure à 2 jours
        if ($alert->isType() && $alert->getDateAlerte() <= $limitDate) {
            // Supprimer l'alerte de la base de données
            $entityManager->remove($alert);
            $entityManager->flush();

            // Afficher un message de succès
            $flashBag->add('success', 'L\'alerte a été supprimée avec succès.');
        } else {
            // Si une condition n'est pas vérifiée, afficher un message d'avertissement
            $flashBag->add('warning', 'Cette alerte ne peut pas être supprimée.');
        }

        // Rediriger vers une page appropriée
        return $this->redirectToRoute('alerte_get');
    }
}
