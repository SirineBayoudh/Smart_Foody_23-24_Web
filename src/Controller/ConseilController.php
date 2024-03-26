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

    #[Route('/updateRepConseil/{id}', name: 'conseil_rep_update')]
    public function update(Request $req, ManagerRegistry $manager, ConseilRepository $repo, $id): Response
    {
        $conseil = $repo->find($id);
        $form = $this->createForm(ConseilBackUpdateType::class, $conseil);
        $form->handleRequest($req);
    
        $em = $manager->getManager();
    
        if ($form->isSubmitted()) {
            $reponse = $form->get('reponse')->getData();
    
            if (empty($reponse)) {
                $this->addFlash('danger', 'Veuillez renseigner le champ de réponse.');
            } else {
                if ($form->isValid()) {
                    $em->flush(); 
                    $this->addFlash('success', 'La réponse a été modifiée avec succès.');
                }
            }
        }
        
        return $this->renderForm('conseil/update.html.twig', [
            'fUpdate' => $form
        ]);
    }
    
    
}
