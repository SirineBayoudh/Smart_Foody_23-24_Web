<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ChatRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\ChatType;

class ChatController extends AbstractController
{
    #[Route('/chat_dash', name: 'chat_listDB')]
    public function getAll(ChatRepository $repo) : Response {

        $list = $repo->findAll();
        return $this->render('chat/index.html.twig', [
            'chats' => $list
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
    public function update(Request $req,ManagerRegistry $manager, ChatRepository $repo, $id): Response{
        $chat = $repo->find($id);
        $form = $this->createForm(ChatType::class,$chat );
        $form->handleRequest($req);

        $em = $manager->getManager();
        if($form->isSubmitted()){
        $em->persist($chat );
        $em->flush();
        }
        
        $list = $repo->findAll();

        return $this->renderForm('chat/update.html.twig', [
            'chats' => $list,
            'fUpdate' => $form
        ]);
    }
}
