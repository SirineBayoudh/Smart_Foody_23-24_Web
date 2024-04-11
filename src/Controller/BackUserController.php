<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ConseillerType;
use App\Form\ProfilConseillerType;
use App\Repository\UtilisateurRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackUserController extends AbstractController
{
    #[Route('/back/user', name: 'app_back_user')]
    public function index(): Response
    {
        return $this->render('back_user/index.html.twig', [
            'controller_name' => 'BackUserController',
        ]);
    }

    /* Afficher la liste des utilisateurs  */

    #[Route('/listUsers', name: 'usersList')]
    public function getAll(Request $request, UtilisateurRepository $repo): Response
    {

        $roleFilter = $request->query->get('role');

        if ($roleFilter) {
            $list = $repo->findByRole($roleFilter);
        } else {
            $list = $repo->findAll();
        }

        return $this->render('back_user/listUsers.html.twig', [
            'users' => $list,
            'role' => $roleFilter
        ]);
    }

    #[Route('/statUsers', name: 'stat_Users')]
    public function statistiques(UtilisateurRepository $repo): Response
    {

        // Comptez le nombre d'hommes et de femmes dans la base de données
        $nombreHommes = $repo->countByGenre('Homme');
        $nombreFemmes = $repo->countByGenre('Femme');


        // Transmettez ces données au modèle
        return $this->render('back_user/statistiquesUser.html.twig', [
            'nombre_hommes' => $nombreHommes,
            'nombre_femmes' => $nombreFemmes,
        ]);
    }


    /* Ajouter un Conseiller */

    #[Route('/ajouterConseiller', name: 'addConseiller')]
    public function addConseiller(ManagerRegistry $manager, Request $req): Response
    {
        $user = new Utilisateur();
        $form = $this->createForm(ConseillerType::class, $user);

        $em = $manager->getManager();

        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {

            $user->setRole('Conseiller');
            $user->setAdresse('');
            $user->setObjectif(null);
            $user->setTentative('0');
            $user->setTaille('0');
            $user->setPoids('0');

            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute("usersList");
        }
        return $this->renderform('back_user/ajouterConseiller.html.twig', ['f' => $form]);
    }

    /* Modifier un Conseiller */

    #[Route('/modifierConseiller/{id}', name: 'conseiller_update')]
    public function updateConseiller(ManagerRegistry $manager, Request $req, UtilisateurRepository $repo, $id): Response
    {

        $user = $repo->find($id);
        $form = $this->createForm(ProfilConseillerType::class, $user);

        $em = $manager->getManager();

        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute("usersList");
        }
        return $this->renderform('back_user/modifierConseiller.html.twig', ['f' => $form]);
    }

    /* Supprimer un Conseiller */

    #[Route('/supprimerConseiller/{id}', name: 'conseiller_delete')]
    public function deleteConseiller(ManagerRegistry $manager, UtilisateurRepository $repo, $id): Response
    {

        $user = $repo->find($id);

        $em = $manager->getManager();

        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute("usersList");
    }
}
