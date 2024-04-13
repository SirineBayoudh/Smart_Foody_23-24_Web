<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ConseillerType;
use App\Form\ProfilConseillerType;
use App\Repository\UtilisateurRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

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
    public function getAll(Request $request, UtilisateurRepository $repo, PaginatorInterface $paginator): Response
    {

        $roleFilter = $request->query->get('role');

        if ($roleFilter) {
            $list = $repo->findByRole($roleFilter);
        } else {
            $list = $repo->findAll();
        }

        $queryBuilder = $repo->createQueryBuilder('u')
            ->orderBy('u.idUtilisateur', 'DESC');

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1), //num page
            5 // nb element par page
        );

        $entityManager = $this->getDoctrine()->getManager();
        
        // Récupérer le nombre de clients
        $clientsCount = $entityManager->getRepository(Utilisateur::class)
            ->createQueryBuilder('u')
            ->select('COUNT(u)')
            ->where('u.role = :role')
            ->setParameter('role', 'client')
            ->getQuery()
            ->getSingleScalarResult();
        
        // Récupérer le nombre de conseillers
        $conseillersCount = $entityManager->getRepository(Utilisateur::class)
            ->createQueryBuilder('u')
            ->select('COUNT(u)')
            ->where('u.role = :role')
            ->setParameter('role', 'conseiller')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('back_user/listUsers.html.twig', [
            'users' => $list,
            'role' => $roleFilter,
            'totalClients' => $clientsCount,
            'totalConseillers' => $conseillersCount,
            'pagination' => $pagination,
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
        $emptySubmission = false;

        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {

            $emptySubmission = true;

            $plainPassword = $user->getMotDePasse();
            $hashedPassword = md5($plainPassword);
            $user->setMotDePasse($hashedPassword);

            $user->setRole('Conseiller');
            $user->setAdresse('');
            $user->setObjectif(null);
            $user->setTentative('0');
            $user->setTaille('0');
            $user->setPoids('0');

            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute("usersList");
        }elseif ($form->isSubmitted()) {
            $emptySubmission = true;
        }
        return $this->renderform('back_user/ajouterConseiller.html.twig', [
            'f' => $form,
            'emptySubmission' => $emptySubmission ?? false,
        ]);
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

    #[Route('/rechercherUsers', name: 'rechercher_utilisateurs')]
    public function rechercher(Request $request): JsonResponse
    {
        $searchText = $request->query->get('searchText');

        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $entityManager->getRepository(Utilisateur::class);

        if (empty($searchText)) {
            $users = $userRepository->findAll();
        } else {
            $users = $userRepository->createQueryBuilder('u')
                ->where('LOWER(u.nom) LIKE :searchText')
                ->setParameter('searchText', '%'.strtolower($searchText).'%')
                ->getQuery()
                ->getResult();
        }

        // Convertit les utilisateurs en tableau associatif pour une sortie JSON
        $response = [];
        foreach ($users as $user) {
            $response[] = [
                'photo' => $user->getPhoto(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'genre' => $user->getGenre(),
                'email' => $user->getEmail(),
                'motDePasse' => $user->getMotDePasse(),
                'numTel' => $user->getNumTel(),
                'role' => $user->getRole(),
                'matricule' => $user->getMatricule(),
                'attestation' => $user->getAttestation(),
                'adresse' => $user->getAdresse(),
                'objectif' => $user->getObjectif() ? $user->getObjectif()->getLibelle() : null,
                'taille' => $user->getTaille(),
                'poids' => $user->getPoids(),
                'idUtilisateur' => $user->getIdUtilisateur(), // Ajoute l'ID de l'utilisateur pour les liens d'édition et de suppression
            ];
        }

        return $this->json([
            'users' => $users,
        ]);
    }
    
}
