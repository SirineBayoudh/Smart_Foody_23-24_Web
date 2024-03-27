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
        $queryBuilder = $repo->createQueryBuilder('a');
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('conseil/index.html.twig', [
            'conseils' => $pagination,
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
        if ($form->isSubmitted()) {
            $em->persist($conseil);
            $em->flush();
            //$twilioService->sendSMS('+21651600246', 'Conseil a été mis à jour avec succès', '+16562282121');
            $this->addFlash('success', 'Demande mis à jour avec succès.');
        }

        $queryBuilder = $repo->createQueryBuilder('a');
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            5
        );

        return $this->renderForm('conseil/update.html.twig', [
            'fUpdate' => $form,
            'conseils' => $pagination
        ]);
    }
}
