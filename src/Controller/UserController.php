<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ClientType;
use App\Form\ConseillerType;
use App\Form\MdpClientType;
use App\Form\MdpConseillerType;
use App\Form\ProfilClientType;
use App\Form\ProfilConseillerType;
use App\Form\ResetPasswordType;
use App\Form\SendEmailType;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use App\Service\CalculComplexite;
use App\Service\EmailBloque;
use App\Service\EmailService;
use Doctrine\ORM\Mapping\Id;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\TwigBundle\DependencyInjection\Compiler\TwigEnvironmentPass;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use App\Service\GeoService;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends AbstractController
{

    private $geoService;

    public function __construct(GeoService $geoService)
    {
        $this->geoService = $geoService;
    }

    #[Route('/login', name: 'login')]
    public function login(Request $request, ManagerRegistry $manager, SessionInterface $session, MailerInterface $mailer, EmailBloque $emailbloque): Response
    {
        if ($request->isMethod('POST')) {

            $email = $request->request->get('email');
            $password = $request->request->get('mot_de_passe');

            // Rechercher l'utilisateur dans la base de données

            $user = $manager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

            if ($user) {
                if ($user->getMotDePasse() == md5($password)) {
                    if ($user->getTentative() >= 3) {
                        $this->addFlash('error', 'Votre compte est verrouillé.');
                    } else {

                        $user->setTentative(0);
                        $manager->getManager()->flush();

                        $session->set('utilisateur', ['idUtilisateur' => $user->getIdUtilisateur(), 'email' => $user->getEmail(), 'role' => $user->getRole()]);

                        if ($user->getRole() == 'Admin') {
                            return $this->redirectToRoute('app_back');
                        } else {
                            return $this->redirectToRoute('accueil');
                        }
                    }
                } else {

                    if ($user->getTentative() < 3) {

                        $user->setTentative($user->getTentative() + 1);
                        $manager->getManager()->flush();

                        if ($user->getTentative() == 3) {

                            $this->addFlash('lockout', 'true');

                            $emailbloque->sendLockoutEmail($user->getEmail(), 'Compte Verrouillé', $user->getPrenom(), $user->getIdUtilisateur());
                            $this->addFlash('error', 'Votre compte a été verrouillé après plusieurs tentatives de connexion échouées.');
                        } else {
                            $this->addFlash('error', 'Mot de passe incorrect. Tentative ' . $user->getTentative() . ' sur 3.');
                        }
                    } else {
                        $this->addFlash('error', 'Votre compte est verrouillé.');
                        // Envoyer un email ici aussi si nécessaire
                    }
                }
            } else {
                $this->addFlash('error', 'Utilisateur non trouvé.');
            }
        }

        // Afficher le formulaire de connexion avec éventuellement un message d'erreur
        return $this->render('security/login.html.twig', []);
    }

    #[Route('/capCam', name: "capture_image")]
    public function captureImage(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $imageData = $data['image'];

        // Supprimer l'en-tête 'data:image/png;base64,' si présent
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif

            // Décodage
            $imageData = base64_decode($imageData);

            // Vous pouvez ici vérifier si le décodage a réussi
            if (!$imageData) {
                throw new \Exception('Base64 decoding failed');
            }
        } else {
            throw new \Exception('Did not match data URI with image data');
        }

        // Nom de fichier pour l'image sauvegardée
        $filename = sprintf('image-%s.%s', uniqid(), $type);

        // Enregistrement de l'image dans un répertoire public
        $filePath = $this->getParameter('kernel.project_dir') . '/public/images/capture/' . $filename;
        if (!file_put_contents($filePath, $imageData)) {
            throw new \Exception('File could not be saved.');
        }

        return $this->json(['status' => 'success', 'path' => $filePath]);
    }

    #[Route('/reactivate', name: 'reactivate_account')]
    public function reactivateAccount(Request $request, ManagerRegistry $manager)
    {
        $userId = $request->query->get('userId');
        $em = $manager->getManager();
        $user = $em->getRepository(Utilisateur::class)->find($userId);

        if (!$user) {
            return new Response('Utilisateur non trouvé', 404);
        }

        $user->setTentative(0);
        $em->flush();

        // Rediriger ou informer l'utilisateur que son compte a été réactivé
        return $this->render('security/account_reactivated.html.twig', [
            'user' => $user
        ]);
    }


    #[Route('/logout', name: 'logout')]
    public function logout(SessionInterface $session): Response
    {
        $session->clear();

        return $this->redirectToRoute('login');
    }

    #[Route('/forgotPassword', name: 'app_forgot_password')]
    public function forgotPassword(ManagerRegistry $manager, Request $request, MailerInterface $mailer, UtilisateurRepository $repo)
    {
        $form = $this->createForm(SendEmailType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData()['email'];

            $user = $repo->findOneByEmail($email);

            if ($user) {

                // Envoi de l'e-mail
                $message = (new Email())
                    ->from('smartfoody.2024@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Reset password')
                    ->html(
                        $this->renderView(
                            'user/email.html.twig',
                            [
                                'user' => $user
                            ]
                        ),
                        'text/html'
                    );

                $mailer->send($message);

                $this->addFlash('envoye', 'Un email de réinitialisation de mot de passe a été envoyé.');
            } else {
                $this->addFlash('nonenvoye', 'Aucun utilisateur trouvé avec cet email.');
            }
        }

        return $this->render('security/forgot_password.html.twig', [
            'emailForm' => $form->createView(),
        ]);
    }

    #[Route('/resetPassword/{id}', name: 'app_reset_password')]
    public function resetPassword(Request $request, $id, UtilisateurRepository $repo)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $repo->find($id);

        if (!$user) {
            $this->addFlash('danger', 'Utilisateur introuvable.');
            return $this->redirectToRoute('login');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data['password'] === $data['confirm_password']) {
                $p = $data['password'];
                $user->setMotDePasse(md5($p));
                $entityManager->flush();

                $this->addFlash('successReset', 'Votre mot de passe a été réinitialisé.');
                return $this->redirectToRoute('login');
            } else {
                $this->addFlash('dangerReset', 'Les mots de passe ne correspondent pas.');
            }
        }

        return $this->render('security/reset_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /** Méthodes pour le client */



    #[Route('/signup', name: 'signup')]
    public function addClient(ManagerRegistry $manager, Request $req, MailerInterface $mailer, EmailService $emailService, UtilisateurRepository $repo, CalculComplexite $calculCmplx): Response
    {

        $ip = '197.3.253.36'; // 31.35.27.169 france // 197.3.253.36 manouba// 197.2.48.207 sousse // 197.0.229.141 sfax
        $countryName = $this->geoService->getCountryNameFromIp($ip);
        $phoneCode = $this->geoService->getPhoneCodeFromCountryName($countryName);
        $flag = $this->geoService->getFlagFromCountryName($countryName);
        $adress = $this->geoService->getAdressFromIP($ip);

        $user = new Utilisateur();
        $form = $this->createForm(ClientType::class, $user, [
            'adresse' => $adress // Passez l'adresse comme option au formulaire
        ]);

        $em = $manager->getManager();

        $form->handleRequest($req);

        $emptySubmission = false;

        $imc = 0;

        if ($form->isSubmitted()) {
            $emptySubmission = true;

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

            $imc = $user->getPoids() / (($user->getTaille() / 100.0) *  ($user->getTaille() / 100.0));

            if ($user->getMotDePasse()) {
                $complexityScore = $calculCmplx->calculateComplexity($user->getMotDePasse());

                if ($complexityScore < 6) {
                    $form->get('motDePasse')->addError(new FormError('Mot de passe faible.'));
                } elseif ($complexityScore >= 6 && $complexityScore < 12) {
                    $form->get('motDePasse')->addError(new FormError('Mot de passe moyen.'));
                } elseif ($complexityScore == 12) {

                    if ($form->isValid()) {

                        $motDePasse = $form->get('motDePasse')->getData();
                        $confirmationMotDePasse = $req->request->get('confirmationMotDePasse'); // Assurez-vous que le nom du champ correspond à celui défini dans votre Twig

                        if ($motDePasse !== $confirmationMotDePasse) {
                            $form->get('motDePasse')->addError(new FormError('Les mots de passe ne correspondent pas.'));
                        } else {
                            if (!$existingUser) {
                                $emptySubmission = true;

                                $plainPassword = $user->getMotDePasse();
                                $hashedPassword = md5($plainPassword);
                                $user->setMotDePasse($hashedPassword);

                                $user->setRole('Client');
                                $user->setMatricule('');
                                $user->setAttestation('');
                                $user->setTentative('0');

                                $em->persist($user);
                                $em->flush();

                                $emailService->sendWelcomeEmail($user->getEmail(), 'Bienvenue', $user->getPrenom());


                                return $this->redirectToRoute("login");
                            } else {
                                $form->get('email')->addError(new \Symfony\Component\Form\FormError('Cette adresse email est déjà utilisée.'));
                            }
                        }
                    }
                }
            }
        }

        return $this->renderform('user/register.html.twig', [
            'f' => $form,
            'country_name' => $countryName,
            'phone_code' => '+' . $phoneCode,
            'flag' => $flag,
            'emptySubmission' => $emptySubmission ?? false,
            'imc' => $imc
        ]);
    }

    /** Profil Client */

    #[Route('/profilClient/{id}', name: 'client_profile')]
    public function updateClient(ManagerRegistry $manager, Request $req, UtilisateurRepository $repo, $id, SessionInterface $session): Response
    {

        $userInfo = $session->get('utilisateur', []);

        // Vérifie si 'idUtilisateur' existe dans le tableau $userInfo
        $userId = $userInfo['idUtilisateur'] ?? null;

        if ($userId) {
            $user = $repo->find($userId);
            $role = $user->getRole();

            if ($role == 'Client') {
                $user = $repo->find($id);
                $form = $this->createForm(ProfilClientType::class, $user);

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

                                $this->addFlash('successPorfilClient', 'Votre profil a été modifié avec succès.');

                                return $this->redirectToRoute("accueil");
                            }
                        }
                    } elseif ($form->isValid()) {

                        $em->persist($user);
                        $em->flush();
                        return $this->redirectToRoute("accueil");
                    }
                }

                return $this->renderform('user/profilClient.html.twig', [
                    'f' => $form,
                    'user' => $user,
                ]);
            }
        } else {
            return $this->renderform('accueil/introuvable.html.twig', []);
        }
    }

    #[Route('/profilClientMDP/{id}', name: 'client_profileMDP')]
    public function updateClientMDP(ManagerRegistry $manager, Request $req, UtilisateurRepository $repo, $id, SessionInterface $session, CalculComplexite $calculCmplx): Response
    {
        $userInfo = $session->get('utilisateur', []);

        // Vérifie si 'idUtilisateur' existe dans le tableau $userInfo
        $userId = $userInfo['idUtilisateur'] ?? null;

        if ($userId) {
            $user = $repo->find($userId);
            $role = $user->getRole();

            if ($role == 'Client') {
                $error = false;

                $user = $repo->find($id);
                $form2 = $this->createForm(MdpClientType::class, $user);

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
                            $form2->get('mot_de_passe')->addError(new FormError('Mot de passe faible.'));
                        } elseif ($complexityScore >= 6 && $complexityScore < 12) {
                            $form2->get('mot_de_passe')->addError(new FormError('Mot de passe moyen.'));
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

                return $this->renderform('user/profilClientMDP.html.twig', [
                    'f2' => $form2,
                    'user' => $user,
                    'error' => $error
                ]);
            }
        } else {
            return $this->renderform('accueil/introuvable.html.twig', []);
        }
    }

    /** Méthodes pour le conseiller*/


    /* Profil Conseiller */

    #[Route('/profilConseiller/{id}', name: 'conseiller_profile')]
    public function updateConseiller(ManagerRegistry $manager, Request $req, UtilisateurRepository $repo, $id, SessionInterface $session): Response
    {

        $userInfo = $session->get('utilisateur', []);

        // Vérifie si 'idUtilisateur' existe dans le tableau $userInfo
        $userId = $userInfo['idUtilisateur'] ?? null;

        if ($userId) {
            $user = $repo->find($userId);
            $role = $user->getRole();

            if ($role == 'Conseiller') {

                $user = $repo->find($id);

                $form = $this->createForm(ProfilConseillerType::class, $user);

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
                                return $this->redirectToRoute("accueil");
                            }
                        }
                    } elseif ($form->isValid()) {

                        $em->persist($user);
                        $em->flush();
                        return $this->redirectToRoute("accueil");
                    }
                }

                return $this->renderform('user/profilConseiller.html.twig', [
                    'f' => $form,
                    'user' => $user,
                ]);
            }
        } else {
            return $this->renderform('accueil/introuvable.html.twig', []);
        }
    }


    #[Route('/profilConseillerMDP/{id}', name: 'conseiller_profileMDP')]
    public function updateConseillerMDP(ManagerRegistry $manager, Request $req, UtilisateurRepository $repo, $id, SessionInterface $session, CalculComplexite $calculCmplx): Response
    {
        $userInfo = $session->get('utilisateur', []);

        // Vérifie si 'idUtilisateur' existe dans le tableau $userInfo
        $userId = $userInfo['idUtilisateur'] ?? null;

        if ($userId) {
            $user = $repo->find($userId);
            $role = $user->getRole();

            if ($role == 'Conseiller') {
                $error = false;

                $user = $repo->find($id);
                $form2 = $this->createForm(MdpConseillerType::class, $user);

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
                            $form2->get('mot_de_passe')->addError(new FormError('Mot de passe faible.'));
                        } elseif ($complexityScore >= 6 && $complexityScore < 12) {
                            $form2->get('mot_de_passe')->addError(new FormError('Mot de passe moyen.'));
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

                return $this->renderform('user/profilConseillerMDP.html.twig', [
                    'f2' => $form2,
                    'user' => $user,
                    'error' => $error
                ]);
            }
        } else {
            return $this->renderform('accueil/introuvable.html.twig', []);
        }
    }


    /** Afficher un utilisateur selon l'id  */

    #[Route('/getOne/{id}', name: 'detail_User')]
    public function getOne(UtilisateurRepository $repo, $id): Response
    {
        $user = $repo->find($id);
        return $this->render('user/detailUser.html.twig', [
            'user' => $user
        ]);
    }
}
