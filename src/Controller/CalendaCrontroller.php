<?php

namespace App\Controller;

use App\Entity\Stock;
use App\Repository\StockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController

{
    #[Route('/afficher-calendrier', name: 'afficher_calendrier')]
    public function afficherCalendrier(StockRepository $stockRepo,  Request $request): Response
    {
        $futureStocks = $stockRepo->findFutureStocks();

        // Utilisez la méthode findExistantStocks avec ou sans terme de recherche
        $searchTerm = $request->query->get('search');

        // Utilisez la méthode findExistantStocks avec ou sans terme de recherche
        $stocks = $stockRepo->findExistantStocks($searchTerm);
        return $this->render('stock/calendar.html.twig', [
            'stocks' => $stocks,
            'futureStocks' => $futureStocks,
        ]);
    }

    #[Route('/edit-date/{id}', name: 'edit_date', methods: ["POST"])]

    public function editDateAction(Request $request, $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Récupérer la nouvelle date depuis la requête
        $newDate = $request->request->get('newDate');

        // Récupérer l'entité Stock à mettre à jour en fonction de l'identifiant
        $stock = $entityManager->getRepository(Stock::class)->find($id);

        if (!$stock) {
            // Gérer le cas où aucune entité Stock correspondant à l'identifiant n'est trouvée
            return new JsonResponse(['success' => false, 'message' => 'Stock not found'], 404);
        }

        // Mettre à jour la date d'arrivage de l'entité Stock avec la nouvelle date
        $stock->setDateArrivage(new \DateTime($newDate));

        // Enregistrer les changements dans la base de données
        $entityManager->flush();

        // Retourner une réponse JSON indiquant le succès de l'opération
        return new JsonResponse(['success' => true, 'message' => 'Stock date updated successfully']);
    }
}
