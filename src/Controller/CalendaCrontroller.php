<?php

namespace App\Controller;


use App\Repository\StockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
}
