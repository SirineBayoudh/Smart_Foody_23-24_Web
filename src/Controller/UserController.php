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
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

class UserController extends AbstractController
{
    /* 
    #[Route('/login', name: 'login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // Récupérer les erreurs de connexion, s'il y en a
        $error = $authenticationUtils->getLastAuthenticationError();
        // Récupérer le dernier email saisi par l'utilisateur
        $lastEmail = $authenticationUtils->getLastUsername();

        if ($this->getUser()) {
            return $this->redirectToRoute('accueil');
        }

        // Afficher le formulaire de connexion avec un éventuel message d'erreur
        return $this->render('user/login.html.twig', [
            'controller_name' => 'UserController',
            'last_email' => $lastEmail,
            'error' => $error,
        ]);
    }
    */

    /** Méthodes pour le client */

    #[Route('/signup', name: 'signup')]
    public function addClient(ManagerRegistry $manager, Request $req, MailerInterface $mailer): Response
    {
        $user = new Utilisateur();
        $form = $this->createForm(ClientType::class, $user);

        $em = $manager->getManager();

        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {

            //$plainPassword = $user->getMotDePasse();
            //$hashedPassword = md5($plainPassword);
            //$user->setMotDePasse($hashedPassword);

            $user->setRole('Client');
            $user->setMatricule('');
            $user->setAttestation('');
            $user->setTentative('0');

            $em->persist($user);
            $em->flush();

           $email = (new Email())
                ->from('smartfoody.2024@gmail.com')
                ->to($user->getEmail())
                ->subject('Bienvenue sur notre site')
                ->html('<p>Bienvenue sur notre site!</p>');

            $mailer->send($email);

            return $this->redirectToRoute("app_login");
        }
        return $this->renderform('user/register.html.twig', ['f' => $form]);
    }

    /** Profil Client */

    #[Route('/profilClient/{id}', name: 'client_profile')]
    public function updateClient(ManagerRegistry $manager, Request $req, UtilisateurRepository $repo, $id): Response
    {

        $user = $repo->find($id);
        $form = $this->createForm(ProfilClientType::class, $user);

        $em = $manager->getManager();

        $form->handleRequest($req);
        if ($form->isSubmitted()) {

            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute("accueil");
        }

        $form2 = $this->createForm(MdpClientType::class, $user);

        $em2 = $manager->getManager();

        $form2->handleRequest($req);
        if ($form2->isSubmitted()) {

            $em2->persist($user);
            $em2->flush();
            return $this->redirectToRoute("app_login");
        }

        return $this->renderform('user/profilClient.html.twig', [
            'f' => $form,
            'f2' => $form2,
            'user' => $user,
        ]);
    }
   
    public function updateClientMDP(ManagerRegistry $manager, Request $req, UtilisateurRepository $repo, $id): Response
    {

        $user = $repo->find($id);
        $form = $this->createForm(MdpClientType::class, $user);

        $em = $manager->getManager();

        $form->handleRequest($req);
        if ($form->isSubmitted()) {

            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute("accueil");
        }
        return $this->renderform('user/mdp.html.twig', [
            'f2' => $form,
            'user' => $user,
        ]);
    }

    /** Méthodes pour le conseiller*/


    /* Profil Conseiller */

    #[Route('/profilConseiller/{id}', name: 'conseiller_profile')]
    public function updateConseiller(ManagerRegistry $manager, Request $req, UtilisateurRepository $repo, $id): Response
    {

        $user = $repo->find($id);
        $form = $this->createForm(ProfilConseillerType::class, $user);

        $em = $manager->getManager();

        $form->handleRequest($req);
        if ($form->isSubmitted()) {

            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute("accueil");
        }

        $form2 = $this->createForm(MdpConseillerType::class, $user);

        $em2 = $manager->getManager();

        $form2->handleRequest($req);
        if ($form2->isSubmitted()) {

            $em2->persist($user);
            $em2->flush();
            return $this->redirectToRoute("app_login");
        }

        return $this->renderform('user/profilConseiller.html.twig', [
            'f' => $form,
            'f2' => $form2,
            'user' => $user,
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
