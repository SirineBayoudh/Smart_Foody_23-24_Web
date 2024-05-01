<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\AdminType;
use App\Form\ConseillerType;
use App\Form\MdpAdminType;
use App\Form\ProfilConseillerType;
use App\Repository\UtilisateurRepository;
use App\Service\CalculComplexite;
use App\Service\EmailService;
use Doctrine\Persistence\ManagerRegistry;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Snappy\Pdf;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
    public function getAll(Request $request, UtilisateurRepository $repo, PaginatorInterface $paginator, SessionInterface $session): Response
    {
        $userInfo = $session->get('utilisateur', []);

        // Vérifie si 'idUtilisateur' existe dans le tableau $userInfo
        $userId = $userInfo['idUtilisateur'] ?? null;

        if ($userId) { 
            $user = $repo->find($userId);
            $role = $user->getRole();

            if ($role == 'Admin') {
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

        } else {
            return $this->render('accueil/introuvable.html.twig', [
                'controller_name' => 'BackController',

            ]);
        }
    }

    #[Route('/userSearch', name: 'user_search')]
    public function search(UtilisateurRepository $repo, Request $request, PaginatorInterface $paginator, SessionInterface $session): Response
    {

        $userInfo = $session->get('utilisateur', []);

        // Vérifie si 'idUtilisateur' existe dans le tableau $userInfo
        $userId = $userInfo['idUtilisateur'] ?? null;

        if ($userId) { 
            $user = $repo->find($userId);
            $role = $user->getRole();

            if ($role == 'Admin') {
                $searchQuery = $request->query->get('q');
    
                if ($searchQuery) {
                    // Effectuer la recherche avec le terme spécifié
                    $queryBuilder = $repo->createQueryBuilder('u');
                    $queryBuilder->where('u.nom LIKE :searchQuery')
                        ->orwhere('u.prenom LIKE :searchQuery')
                        ->orwhere('u.email LIKE :searchQuery')
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
                        5
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
        } else {
            return $this->render('accueil/introuvable.html.twig', [
                'controller_name' => 'BackController',

            ]);
        }
    }


    #[Route('/statUsers', name: 'stat_Users')]
    public function statistiques(UtilisateurRepository $repo, SessionInterface $session): Response
    {
        $userInfo = $session->get('utilisateur', []);

        // Vérifie si 'idUtilisateur' existe dans le tableau $userInfo
        $userId = $userInfo['idUtilisateur'] ?? null;

        if ($userId) { 
            $user = $repo->find($userId);
            $role = $user->getRole();

            if ($role == 'Admin') {
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

        } else {
            return $this->render('accueil/introuvable.html.twig', [
                'controller_name' => 'BackController',

            ]);
        }
    }


    /* Ajouter un Conseiller */

    #[Route('/ajouterConseiller', name: 'addConseiller')]
    public function addConseiller(ManagerRegistry $manager, Request $req, UtilisateurRepository $repo, SessionInterface $session, EmailService $emailService, CalculComplexite $calculCmplx): Response
    {
        
        $userInfo = $session->get('utilisateur', []);

        // Vérifie si 'idUtilisateur' existe dans le tableau $userInfo
        $userId = $userInfo['idUtilisateur'] ?? null;

        if ($userId) { 
            $user = $repo->find($userId);
            $role = $user->getRole();

            if ($role == 'Admin') {

                $user = new Utilisateur();
                $form = $this->createForm(ConseillerType::class, $user);
    
                $emptySubmission = false;
    
                $photo = $repo->getAdminImage();
    
    
                $em = $manager->getManager();
    
                $form->handleRequest($req);
    
    
                if ($form->isSubmitted()) {
    
                    if ($user->getMotDePasse()) {
                        $complexityScore = $calculCmplx->calculateComplexity($user->getMotDePasse());
    
                        if ($complexityScore < 6) {
                            $form->get('motDePasse')->addError(new FormError('Mot de passe faible.'));
                        } elseif ($complexityScore >= 6 && $complexityScore < 12) {
                            $form->get('motDePasse')->addError(new FormError('Mot de passe moyen.'));
                        } elseif ($complexityScore == 12) {
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
    
                                    $emailService->sendWelcomeEmail($user->getEmail(), 'Bienvenue', $user->getPrenom());
    
                                    return $this->redirectToRoute("usersList");
                                } else {
                                    $form->get('email')->addError(new \Symfony\Component\Form\FormError('Cette adresse email est déjà utilisée.'));
                                }
                            }
                        }
                    }
                }
                return $this->renderform('back_user/ajouterConseiller.html.twig', [
                    'f' => $form,
                    'emptySubmission' => $emptySubmission ?? false,
                    'photo' => $photo
                ]);
            }
        } else {
            return $this->render('accueil/introuvable.html.twig', [
                'controller_name' => 'BackController',

            ]);
        }
    }


    /* Modifier un Conseiller */

    #[Route('/modifierConseiller/{id}', name: 'conseiller_update')]
    public function updateConseiller(ManagerRegistry $manager, Request $req, UtilisateurRepository $repo, $id, SessionInterface $session): Response
    {

        $userInfo = $session->get('utilisateur', []);

        // Vérifie si 'idUtilisateur' existe dans le tableau $userInfo
        $userId = $userInfo['idUtilisateur'] ?? null;

        if ($userId) { 

            $user = $repo->find($userId);
            $role = $user->getRole();

            if ($role == 'Admin') {

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
        } else {
            return $this->render('accueil/introuvable.html.twig', [
                'controller_name' => 'BackController',

            ]);
        }
    }

    /* Supprimer un Conseiller */

    #[Route('/supprimerConseiller/{id}', name: 'conseiller_delete')]
    public function deleteConseiller(ManagerRegistry $manager, UtilisateurRepository $repo, $id, SessionInterface $session): Response
    {
        $userInfo = $session->get('utilisateur', []);

        // Vérifie si 'idUtilisateur' existe dans le tableau $userInfo
        $userId = $userInfo['idUtilisateur'] ?? null;

        if ($userId) { 
            
            $user = $repo->find($userId);
            $role = $user->getRole();

            if ($role == 'Admin') {
                $user = $repo->find($id);
    
                $em = $manager->getManager();
    
                $em->remove($user);
                $em->flush();
                return $this->redirectToRoute("usersList");
            }
        } else {
            return $this->render('accueil/introuvable.html.twig', [
                'controller_name' => 'BackController',

            ]);
        }
    }

    

    #[Route('/profilAdmin/{id}', name: 'admin_profile')]
    public function updateAdmin(ManagerRegistry $manager, Request $req, UtilisateurRepository $repo, $id, SessionInterface $session): Response
    {

        $userInfo = $session->get('utilisateur', []);

        // Vérifie si 'idUtilisateur' existe dans le tableau $userInfo
        $userId = $userInfo['idUtilisateur'] ?? null;

        if ($userId) { 
            
            $user = $repo->find($userId);
            $role = $user->getRole();
            $photo = $repo->getAdminImage();

            if ($role == 'Admin') {

                $user = $repo->find($id);
    
                $form = $this->createForm(AdminType::class, $user);
    
                $emailExistant = $user->getEmail();
    
                $em = $manager->getManager();
    
                $form->handleRequest($req);
    
                if ($form->isSubmitted()) {
    
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
    
                                $this->addFlash('successPorfilCAdmin', 'Votre profil a été modifié avec succès.');
    
                                return $this->redirectToRoute("accueil");
                            }
                        }
                    } elseif ($form->isValid()) {
    
                        $em->persist($user);
                        $em->flush();
                        return $this->redirectToRoute("app_back");
                    }
                }
    
                return $this->renderform('back_user/profilAdmin.html.twig', [
                    'f' => $form,
                    'user' => $user,
                    'photo' => $photo
                ]);
            }

        } else {
            return $this->renderform('accueil/introuvable.html.twig', []);
        }
    }

    #[Route('/profilAdminMDP/{id}', name: 'admin_profileMDP')]
    public function updateAdminMDP(ManagerRegistry $manager, Request $req, UtilisateurRepository $repo, $id, SessionInterface $session, CalculComplexite $calculCmplx): Response
    {
        $userInfo = $session->get('utilisateur', []);

        // Vérifie si 'idUtilisateur' existe dans le tableau $userInfo
        $userId = $userInfo['idUtilisateur'] ?? null;

        if ($userId) { 
            
            $user = $repo->find($userId);
            $role = $user->getRole();
            $photo = $repo->getAdminImage();

            if ($role == 'Admin') {
                $error = false;
    
                $user = $repo->find($id);
                $form2 = $this->createForm(MdpAdminType::class, $user);
    
                $em2 = $manager->getManager();
    
                $form2->handleRequest($req);
    
                $ancMDP = $req->request->get('ancienMDP');
                dump($ancMDP);
    
                $mdpActuel = $repo->getPasswordByEmail($user->getEmail());
                dump($mdpActuel);
    
    
                if ($form2->isSubmitted()) {
                    if ($user->getMotDePasse()) {
                        $complexityScore = $calculCmplx->calculateComplexity($user->getMotDePasse());
    
                        if ($complexityScore < 6) {
                            $form2->get('motDePasse')->addError(new FormError('Mot de passe faible.'));
                        } elseif ($complexityScore >= 6 && $complexityScore < 12) {
                            $form2->get('motDePasse')->addError(new FormError('Mot de passe moyen.'));
                        } elseif ($complexityScore == 12) {
                            if ($form2->isValid()) {
                                if (md5($ancMDP) == $mdpActuel) {
    
                                    $plainPassword = $user->getMotDePasse();
                                    $hashedPassword = md5($plainPassword);
                                    $user->setMotDePasse($hashedPassword);
    
                                    $em2->persist($user);
                                    $em2->flush();
                                    return $this->redirectToRoute("login");
                                } else {
                                    $error = true;
                                }
                            }
                        }
                    }
                }
    
                return $this->renderform('back_user/profilAdminMDP.html.twig', [
                    'f' => $form2,
                    'user' => $user,
                    'error' => $error,
                    'photo' => $photo
                ]);
            }

        } else {
            return $this->renderform('accueil/introuvable.html.twig', []);
        }
    }

    #[Route('/pdfClients', name: 'clients_pdf')]
    public function clientsPdf(UtilisateurRepository $repo): Response
    {
        // Récupération des utilisateurs
        $clients = $repo->findByRole('Client');

        // Options de Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->setIsRemoteEnabled(true);

        // Instanciation de Dompdf avec les options
        $dompdf = new Dompdf($pdfOptions);

        // Rendu du HTML avec le template Twig
        $html = $this->renderView('back_user/clients_pdf.html.twig', [
            'clients' => $clients
        ]);

        // Chargement du HTML dans Dompdf
        $dompdf->loadHtml($html);

        // Paramétrage (A4 en portrait par défaut)
        $dompdf->setPaper('A4', 'portrait');

        // Rendu du PDF
        $dompdf->render();

        // Envoi du fichier PDF au navigateur
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="liste_des_utilisateurs.pdf"'
        ]);
    }

    #[Route('/pdfConseillers', name: 'conseillers_pdf')]
    public function conseillersPdf(UtilisateurRepository $repo): Response
    {
        // Récupération des utilisateurs
        $conseillers = $repo->findByRole('Conseiller');
        // Options de Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->setIsRemoteEnabled(true);

        // Instanciation de Dompdf avec les options
        $dompdf = new Dompdf($pdfOptions);

        // Rendu du HTML avec le template Twig
        $html = $this->renderView('back_user/conseillers_pdf.html.twig', [
            'conseillers' => $conseillers
        ]);

        // Chargement du HTML dans Dompdf
        $dompdf->loadHtml($html);

        // Paramétrage (A4 en portrait par défaut)
        $dompdf->setPaper('A4', 'portrait');

        // Rendu du PDF
        $dompdf->render();

        // Envoi du fichier PDF au navigateur
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="liste_des_utilisateurs.pdf"'
        ]);
    }
}
