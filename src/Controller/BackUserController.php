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
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class BackUserController extends AbstractController
{
    #[Route('/back/user', name: 'app_back_user')]
    public function index(UtilisateurRepository $repo): Response
    {

        $photo = $repo->getAdminImage();

        return $this->render('back_user/index.html.twig', [
            'controller_name' => 'BackUserController',
            'photo' => $photo
        ]);
    }

    /* Afficher la liste des utilisateurs  */

    #[Route('/listUsers', name: 'usersList')]
    public function getAll(Request $request, UtilisateurRepository $repo, PaginatorInterface $paginator): Response
    {

        $roleFilter = $request->query->get('role');
        $query = $request->query->get('query');

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

        $photo = $repo->getAdminImage();

        $query = $request->query->get('query');

        return $this->render('back_user/listUsers.html.twig', [
            'users' => $pagination,
            'role' => $roleFilter,
            'totalClients' => $clientsCount,
            'totalConseillers' => $conseillersCount,
            'pagination' => $pagination,
            'photo' => $photo,
            'query' => $query
        ]);
    }

    #[Route('/userSearch', name: 'user_search')]
    public function search(UtilisateurRepository $repo, Request $request, PaginatorInterface $paginator): Response
    {
        $searchQuery = $request->query->get('q');

        if ($searchQuery) {
            // Effectuer la recherche avec le terme spécifié
            $queryBuilder = $repo->createQueryBuilder('u');
            $queryBuilder->where('u.nom LIKE :searchQuery')
                ->orwhere('u.prenom LIKE :searchQuery')
                ->setParameter('searchQuery', '%' . $searchQuery . '%');

            $pagination = $paginator->paginate(
                $queryBuilder->getQuery(),
                $request->query->getInt('page', 1),
                2
            );

            // Récupérer les stocks paginés
            $users = $pagination;
        } else {
            // Si aucune requête de recherche n'est spécifiée, récupérer tous les stocks
            $pagination = $paginator->paginate(
                $repo->findAll(),
                $request->query->getInt('page', 1),
                2
            );

            // Récupérer les stocks paginés
            $users = $pagination;
        }

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

        $photo = $repo->getAdminImage();

        return $this->render('back_user/listUsers.html.twig', [
            'users' => $users,
            'searchQuery' => $searchQuery,
            'pagination' => $pagination,
            'photo' => $photo,
            'totalClients' => $clientsCount,
            'totalConseillers' => $conseillersCount,
        ]);
    }


    #[Route('/statUsers', name: 'stat_Users')]
    public function statistiques(UtilisateurRepository $repo): Response
    {

        // Comptez le nombre d'hommes et de femmes dans la base de données
        $nbFemme = $repo->getCountByGender('Femme');
        $nbHomme = $repo->getCountByGender('Homme');

        $nbBienEtre = $repo->getCountByObjectif('1');
        $nbPrisePoids = $repo->getCountByObjectif('2');
        $nbPertePoids = $repo->getCountByObjectif('3');
        $nbPriseMasse = $repo->getCountByObjectif('4');

        $nbClients = $repo->getCountByRole('Client');
        $nbConseillers = $repo->getCountByRole('Conseiller');



        $photo = $repo->getAdminImage();
        // Transmettez ces données au modèle
        return $this->render('back_user/statistiquesUser.html.twig', [
            'nbFemme' => $nbFemme,
            'nbHomme' => $nbHomme,
            'nbBienEtre' => $nbBienEtre,
            'nbPrisePoids' => $nbPrisePoids,
            'nbPertePoids' => $nbPertePoids,
            'nbPriseMasse' => $nbPriseMasse,
            'nbClients' => $nbClients,
            'nbConseillers' => $nbConseillers,
            'photo' => $photo
        ]);
    }


    /* Ajouter un Conseiller */

    #[Route('/ajouterConseiller', name: 'addConseiller')]
    public function addConseiller(ManagerRegistry $manager, Request $req, UtilisateurRepository $repo): Response
    {
        $user = new Utilisateur();
        $form = $this->createForm(ConseillerType::class, $user);

        $emptySubmission = false;

        $photo = $repo->getAdminImage();


        $em = $manager->getManager();

        $form->handleRequest($req);


        if ($form->isSubmitted()) {

            $file = $form->get('attestation')->getData();  
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // Cela sert à donner un nom unique à chaque fichier pour éviter les conflits de nom
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $file->guessExtension();

                // Assurez-vous que l'extension est correcte pour un PDF
                if ($file->guessExtension() !== 'pdf') {
                    throw new \Exception("Le fichier n'est pas un PDF valide.");
                }

                // Déplace le fichier dans le répertoire où sont stockés les fichiers PDF
                try {
                    $file->move(
                        $this->getParameter('pdf_directory'),  // Assurez-vous que ce paramètre est bien défini dans votre configuration
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'exception si le fichier ne peut pas être déplacé
                    // Par exemple : enregistrer un message d'erreur dans un log ou afficher un message à l'utilisateur
                }

                // Met à jour le nom du fichier PDF dans l'entité correspondante, par exemple un utilisateur ou un document
                $user->setAttestation($newFilename);
            }


            $imageFile = $form->get('photo')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Cela sert à donner un nom unique à chaque image pour éviter les conflits de nom
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                // Déplace le fichier dans le répertoire où sont stockées les images
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'exception si le fichier ne peut pas être déplacé
                }
                // Met à jour le nom de l'image dans l'entité Produit
                $user->setPhoto($newFilename);
            }

            $email = $form->get('email')->getData();

            $existingUser = $repo->findByEmail($email);
            $emptySubmission = true;

            if ($form->isValid()) {

                if (!$existingUser) {

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


                    $this->addFlash('success', 'Conseiller ajouté avec succès');

                    return $this->redirectToRoute("usersList");
                } else {
                    $form->get('email')->addError(new \Symfony\Component\Form\FormError('Cette adresse email est déjà utilisée.'));
                }
            }
        }
        return $this->renderform('back_user/ajouterConseiller.html.twig', [
            'f' => $form,
            'emptySubmission' => $emptySubmission ?? false,
            'photo' => $photo
        ]);
    }


    /* Modifier un Conseiller */

    #[Route('/modifierConseiller/{id}', name: 'conseiller_update')]
    public function updateConseiller(ManagerRegistry $manager, Request $req, UtilisateurRepository $repo, $id): Response
    {

        $user = $repo->find($id);
        $form = $this->createForm(ProfilConseillerType::class, $user);

        $photo = $repo->getAdminImage();

        $emailExistant = $user->getEmail();

        $em = $manager->getManager();

        $form->handleRequest($req);

        if ($form->isSubmitted()) {
            $file = $form->get('attestation')->getData();  
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // Cela sert à donner un nom unique à chaque fichier pour éviter les conflits de nom
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $file->guessExtension();

                // Assurez-vous que l'extension est correcte pour un PDF
                if ($file->guessExtension() !== 'pdf') {
                    throw new \Exception("Le fichier n'est pas un PDF valide.");
                }

                // Déplace le fichier dans le répertoire où sont stockés les fichiers PDF
                try {
                    $file->move(
                        $this->getParameter('pdf_directory'),  // Assurez-vous que ce paramètre est bien défini dans votre configuration
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'exception si le fichier ne peut pas être déplacé
                    // Par exemple : enregistrer un message d'erreur dans un log ou afficher un message à l'utilisateur
                }

                // Met à jour le nom du fichier PDF dans l'entité correspondante, par exemple un utilisateur ou un document
                $user->setAttestation($newFilename);
            }


            $imageFile = $form->get('photo')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Cela sert à donner un nom unique à chaque image pour éviter les conflits de nom
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                // Déplace le fichier dans le répertoire où sont stockées les images
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'exception si le fichier ne peut pas être déplacé
                }
                // Met à jour le nom de l'image dans l'entité Produit
                $user->setPhoto($newFilename);
            }

            $emailNV = $user->getEmail();

            if ($emailExistant != $emailNV) {

                $existingUser = $repo->findByEmail($emailNV);

                if ($existingUser) {
                    $form->get('email')->addError(new \Symfony\Component\Form\FormError('Cette adresse email est déjà utilisée.'));
                } else {
                    if ($form->isValid()) {

                        $em->persist($user);
                        $em->flush();
                        $this->addFlash('success', 'Conseiller modifié avec succès');

                        return $this->redirectToRoute("usersList");
                    }
                }
            } elseif ($form->isValid()) {

                $em->persist($user);
                $em->flush();
                $this->addFlash('success', 'Conseiller modifié avec succès');

                return $this->redirectToRoute("usersList");
            }
        }

        return $this->renderform('back_user/modifierConseiller.html.twig', [
            'f' => $form,
            'photo' => $photo
        ]);
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
                ->setParameter('searchText', '%' . strtolower($searchText) . '%')
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

    #[Route('/pdfUsers', name: 'export_pdf')]
    public function usersListPdf(Pdf $pdf, UtilisateurRepository $repo): Response
    {
        // Récupérer tous les utilisateurs depuis la base de données
        //$userRepository = $this->getDoctrine()->getRepository(Utilisateur::class);
        $users = $repo->findAll();

        // Rendre la vue Twig pour le contenu PDF
        $html = $this->renderView('back_user/users_pdf.html.twig', [
            'users' => $users,
        ]);

        // Générer le PDF à partir du HTML
        $pdfContent = $pdf->getOutputFromHtml($html);

        // Créer une réponse PDF
        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');

        // Télécharger le PDF ou l'afficher dans le navigateur selon vos besoins
        // Par exemple, pour le télécharger :
        $response->headers->set('Content-Disposition', 'attachment; filename="users_list.pdf"');

        return $response;
    }
}
