<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ClientType;
use App\Form\ConseillerType;
use App\Form\MdpClientType;
use App\Form\MdpConseillerType;
use App\Form\ProfilClientType;
use App\Form\ProfilConseillerType;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use App\Service\EmailService;
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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

class UserController extends AbstractController
{

    #[Route('/login', name: 'login')]
    public function login(Request $request, ManagerRegistry $manager): Response
    {
        $error = '';

        if ($request->isMethod('POST')) {

            $email = $request->request->get('email');
            $password = $request->request->get('mot_de_passe');

            // Rechercher l'utilisateur dans la base de données

            $user = $manager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

            if ($user) {
                if ($user->getMotDePasse() == md5($password)) {
                    dump("we found it");

                    if ($user->getRole() == 'Admin') {

                        return $this->redirectToRoute('app_back');
                    } else {

                        return $this->redirectToRoute('accueil');
                    }
                } else {
                    dump("we didn't found it");

                    $error = 'mot de passe incorrect';
                }
            } else {
                $error = 'Utilisateur non trouvé';
            }
        }

        // Afficher le formulaire de connexion avec éventuellement un message d'erreur
        return $this->render('security/login.html.twig', [
            'error' => $error,
        ]);
    }

    /** Méthodes pour le client */

    #[Route('/signup', name: 'signup')]
    public function addClient(ManagerRegistry $manager, Request $req, MailerInterface $mailer, EmailService $emailService, UtilisateurRepository $repo): Response
    {
        $user = new Utilisateur();
        $form = $this->createForm(ClientType::class, $user);

        $em = $manager->getManager();

        $form->handleRequest($req);


        if ($form->isSubmitted()) {
            $email = $form->get('email')->getData();

            $existingUser = $repo->findByEmail($email);


            if ($form->isValid()) {

                if (!$existingUser) {

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

        return $this->renderform('user/register.html.twig', ['f' => $form]);
    }

    /** Profil Client */

    #[Route('/profilClient/{id}', name: 'client_profile')]
    public function updateClient(ManagerRegistry $manager, Request $req, UtilisateurRepository $repo, $id): Response
    {

        $user = $repo->find($id);
        $form = $this->createForm(ProfilClientType::class, $user);

        $emailExistant = $user->getEmail();

        $em = $manager->getManager();

        $form->handleRequest($req);

        if ($form->isSubmitted()) {

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

        return $this->renderform('user/profilClient.html.twig', [
            'f' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/profilClientMDP/{id}', name: 'client_profileMDP')]
    public function updateClientMDP(ManagerRegistry $manager, Request $req, UtilisateurRepository $repo, $id): Response
    {

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

        return $this->renderform('user/profilClientMDP.html.twig', [
            'f2' => $form2,
            'user' => $user,
            'error' => $error
        ]);
    }

    /** Méthodes pour le conseiller*/


    /* Profil Conseiller */

    #[Route('/profilConseiller/{id}', name: 'conseiller_profile')]
    public function updateConseiller(ManagerRegistry $manager, Request $req, UtilisateurRepository $repo, $id): Response
    {

        $user = $repo->find($id);
        $form = $this->createForm(ProfilConseillerType::class, $user);

        $emailExistant = $user->getEmail();

        $em = $manager->getManager();

        $form->handleRequest($req);

        if ($form->isSubmitted()) {

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


    #[Route('/profilConseillerMDP/{id}', name: 'conseiller_profileMDP')]
    public function updateConseillerMDP(ManagerRegistry $manager, Request $req, UtilisateurRepository $repo, $id): Response
    {
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

        return $this->renderform('user/profilConseillerMDP.html.twig', [
            'f2' => $form2,
            'user' => $user,
            'error' => $error
        ]);
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
