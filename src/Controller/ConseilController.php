<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ConseilRepository;
use App\Entity\Conseil;
use App\Form\ConseilBackUpdateType;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use App\Service\TwilioService;

class ConseilController extends AbstractController
{
    #[Route('/conseil_dash', name: 'conseil_listDB')]
    public function getAll(ConseilRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        $statut = $request->query->get('statut');
        $note = $request->query->get('note');

        $queryBuilder = $repo->createQueryBuilder('a')
            ->orderBy('a.id_conseil', 'DESC');

        if ($statut !== null && $statut !== 'Tous') {
            $queryBuilder->andWhere('a.statut = :statut')
                ->setParameter('statut', $statut);
        }

        if ($note !== null && $note !== '') {
            $queryBuilder->andWhere('a.note = :note')
                ->setParameter('note', $note);
        }

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            5
        );

        $averageRating = $repo->getAverageRating();
        $totalConseils = $repo->getTotalConseils();
        $conseilsEnAttente = $repo->getCountByStatut('en attente');
        $conseilsTermines = $repo->getCountByStatut('terminé');
        $notesCount = $repo->getNotesCount();

        return $this->render('conseil/index.html.twig', [
            'conseils' => $pagination,
            'totalConseils' => $totalConseils,
            'conseilsEnAttente' => $conseilsEnAttente,
            'conseilsTermines' => $conseilsTermines,
            'notesCount' => $notesCount,
            'averageRating' => $averageRating
        ]);
    }


    #[Route('/deleteConseil/{id}', name: 'conseil_delete')]
    public function deleteConseil(ManagerRegistry $manager, ConseilRepository $repo, $id): Response
    {
        $conseil = $repo->find($id);
        $em = $manager->getManager();
        $em->remove($conseil);
        $em->flush();

        return $this->redirectToRoute("conseil_listDB");
    }

    #[Route('/updateRepConseil/{id}', name: 'conseil_rep_update')]
    public function update(Request $req, ManagerRegistry $manager, ConseilRepository $repo, $id, PaginatorInterface $paginator, Request $request, TwilioService $twilioService): Response
    {
        $conseil = $repo->find($id);
        $form = $this->createForm(ConseilBackUpdateType::class, $conseil);
        $form->handleRequest($req);

        $em = $manager->getManager();
        if ($form->isSubmitted() & empty($form->get('reponse')->getData())) {
            $emptySubmission = true;
        } elseif ($form->isSubmitted() && $form->isValid()) {
            $conseil->setStatut('terminé');
            $em->persist($conseil);
            $em->flush();
            //$twilioService->sendSMS('+21651600246', 'Conseil a été mis à jour avec succès.', '+16562282121');
            $this->addFlash('success', 'Demande mis à jour avec succès.');
        }

        $queryBuilder = $repo->createQueryBuilder('a')
            ->orderBy('a.id_conseil', 'DESC');
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            5
        );

        return $this->renderForm('conseil/update.html.twig', [
            'fUpdate' => $form,
            'emptySubmission' => $emptySubmission ?? false,
            'conseils' => $pagination
        ]);
    }
}
