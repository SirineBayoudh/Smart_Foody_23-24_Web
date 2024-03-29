<?php

namespace App\Controller;

use App\Form\ConseilType;
use App\Form\ConseilUpdateType;
use App\Entity\Conseil;
use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\ConseilRepository;
use App\Service\CalorieNinjasService;
use App\Service\QuotableService;
use App\Repository\ChatRepository;

class ConseilFrontController extends AbstractController
{
    #[Route('/conseil', name: 'conseil_app')]
    public function addConseil(ChatRepository $repo,Request $req, ManagerRegistry $manager, ConseilRepository $conseilRepository, Request $request, CalorieNinjasService $calorieNinjasService, QuotableService $quotableService): Response
    {
        $conseil = new Conseil();
        $conseil->setStatut('en attente');
        $conseil->setReponse('');
        $conseil->setNote(null);
        $utilisateur = $this->getDoctrine()->getRepository(Utilisateur::class)->find(2); //STATIQUE
        $conseil->setIdClient($utilisateur);
        $conseil->setDateConseil(new \DateTime());
        $form = $this->createForm(ConseilType::class, $conseil);
        $form->handleRequest($req);

        $em = $manager->getManager();

        $numberOfConseils = $conseilRepository->countConseilsForUserPerDay($utilisateur->getId());

        if ($form->isSubmitted() && $numberOfConseils >= 3) {
            $max = true;
        } else if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($conseil);
            $em->flush();
            $success = true;
        } elseif ($form->isSubmitted()) {
            $emptySubmission = true;
        }

        $conseils = $this->getDoctrine()->getRepository(Conseil::class)->findBy(['id_client' => 2]);  //STATIQUE

        $food = $request->query->get('food');
        $calories = null;

        if ($food) {
            $calories = $calorieNinjasService->getCaloriesForFood($food);
        }

        $randomQuote = $quotableService->getRandomQuote();

        $list = $repo->findAll();

        return $this->renderForm('conseil_front/add.html.twig', [
            'f' => $form,
            'conseils' => $conseils,
            'success' => $success ?? false,
            'max' => $max ?? false,
            'emptySubmission' => $emptySubmission ?? false,
            'numberOfConseils' => $numberOfConseils,
            'food' => $food,
            'randomQuote' => $randomQuote,
            'chats' => $list,
            'calories' => $calories
        ]);
    }

    #[Route('/updateNoteConseil/{id_conseil}', name: 'conseil_note_update')]
    public function update(Request $req, ManagerRegistry $manager, ConseilRepository $repo, $id_conseil, CalorieNinjasService $calorieNinjasService, Request $request, QuotableService $quotableService): Response
    {
        $conseil = $repo->find($id_conseil);
        $form = $this->createForm(ConseilUpdateType::class, $conseil);
        $form->handleRequest($req);

        $em = $manager->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'La note a été modifiée avec succès.');
        }

        $conseils = $this->getDoctrine()->getRepository(Conseil::class)->findBy(['id_client' => 2]);  //STATIQUE

        $food = $request->query->get('food');
        $calories = null;

        if ($food) {
            $calories = $calorieNinjasService->getCaloriesForFood($food);
        }

        $randomQuote = $quotableService->getRandomQuote();

        return $this->renderForm('conseil_front/update.html.twig', [
            'conseils' => $conseils,
            'food' => $food,
            'randomQuote' => $randomQuote,
            'fUpdate' => $form,
            'calories' => $calories
        ]);
    }
}
