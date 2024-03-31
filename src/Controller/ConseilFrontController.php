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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ConseilFrontController extends AbstractController
{
    #[Route('/conseil', name: 'conseil_app')]
    public function addConseil(MailerInterface $mailer, ChatRepository $repo, Request $req, ManagerRegistry $manager, ConseilRepository $conseilRepository, Request $request, CalorieNinjasService $calorieNinjasService, QuotableService $quotableService): Response
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
            $demande = $conseil->getDemande();
            $dateConseil = $conseil->getDateConseil()->format('Y-m-d H:i:s');

            $emailContent = "
            <html>
                <body>
                    <div style='width: 500px; background-color: #aad597; padding: 20px;'>
                        <p style='color: darkgreen; font-size: 14px;'>Bonjour,</p>
                        <p style='color: darkgreen; font-size: 18px;'>Une nouvelle demande a été reçue :</p>
                        <p style='color: black; font-weight: bold; font-size: 16px;'>Objet: " . $demande . "</p>
                        <p style='color: darkgreen; font-weight: bold; font-size: 16px;'>Date de réception: " . $dateConseil . "</p>
                        <p style='font-weight: bold;'>Veuillez consulter l'application pour la traiter.</p>
                        <p style='color: gray;'>Ceci est un message automatique. Merci de ne pas répondre.</p>
                    </div>
                </body>
            </html>
            ";

            $email = (new Email())
                ->from('smartfoody.2024@gmail.com')
                ->to('yassiine.studies@gmail.com')
                ->subject('Smart Foody : Nouvelle demande reçue')
                ->html($emailContent);

            $mailer->send($email);

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
