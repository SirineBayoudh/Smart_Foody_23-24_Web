<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ConseilRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConseilController extends AbstractController
{
    #[Route('/conseil_dash', name: 'conseil_listDB')]
    public function getAll(ConseilRepository $repo) : Response {

        $list = $repo->findAll();
        return $this->render('conseil/index.html.twig', [
            'conseils' => $list
        ]);
    }

    #[Route('/deleteConseil/{id}', name: 'conseil_delete')]
    public function deleteConseil(ManagerRegistry $manager, ConseilRepository $repo, $id) : Response {
        $conseil = $repo->find($id);
        $em = $manager->getManager();
        $em->remove($conseil);
        $em->flush();

        return $this->redirectToRoute("conseil_listDB");
    }
}
