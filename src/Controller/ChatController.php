<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ChatRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use App\Form\ChatType;
use App\Entity\Chat;

class ChatController extends AbstractController
{
    #[Route('/chat_dash', name: 'chat_listDB')]
    public function getAll(Request $req, ManagerRegistry $manager, ChatRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {

        $queryBuilder = $repo->createQueryBuilder('a')
        ->orderBy('a.id_chat', 'DESC');
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            5
        );

        $chat = new Chat();
        $form = $this->createForm(ChatType::class, $chat);
        $form->handleRequest($req);

        $em = $manager->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($chat);
            $em->flush();
            $this->addFlash('success', 'Ajout avec succès.');
            return $this->redirectToRoute('chat_listDB');
        } elseif ($form->isSubmitted()) {
            $emptySubmission = true;
        }
        return $this->renderForm('chat/index.html.twig', [
            'f' => $form,
            'emptySubmission' => $emptySubmission ?? false,
            'chats' => $pagination
        ]);
    }

    #[Route('/deleteChat/{id}', name: 'chat_delete')]
    public function deleteChat(ManagerRegistry $manager, ChatRepository $repo, $id): Response
    {
        $chat = $repo->find($id);
        $em = $manager->getManager();
        $em->remove($chat);
        $em->flush();

        return $this->redirectToRoute("chat_listDB");
    }


    #[Route('/updateChat/{id}', name: 'chat_update')]
    public function update(Request $req, ManagerRegistry $manager, ChatRepository $repo, $id, PaginatorInterface $paginator): Response
    {
        $chat = $repo->find($id);
        $form = $this->createForm(ChatType::class, $chat);
        $form->handleRequest($req);

        $em = $manager->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($chat);
            $em->flush();
            $this->addFlash('success', 'Mise à jour effectuée avec succès.');
        } elseif ($form->isSubmitted()) {
            $emptySubmission = true;
        }

        $list = $repo->findAll();

        $queryBuilder = $repo->createQueryBuilder('a')
        ->orderBy('a.id_chat', 'DESC');
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $req->query->getInt('page', 1),
            5
        );

        return $this->renderForm('chat/update.html.twig', [
            'chats' => $pagination,
            'emptySubmission' => $emptySubmission ?? false,
            'fUpdate' => $form
        ]);
    }
}
