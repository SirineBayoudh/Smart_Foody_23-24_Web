<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendWelcomeEmail(string $to, string $subject, string $prenom)
    {

        $email = (new Email())
            ->from('smartfoody.2024@gmail.com')
            ->to($to)
            ->subject($subject)
            ->html("
            <html>
                <head>
                    <style>
                        .container {
                            width: 500px;
                            padding: 20px;
                            background-color: #f7f7f7;
                            border-radius: 10px;
                            margin: 0 auto;
                        }
                        .header {
                            text-align: center;
                            margin-bottom: 20px;
                        }
                        .logo {
                            width: 100px;
                            height: auto;
                        }
                        .content {
                            color: #000;
                            font-size: 14px;
                        }
                        .footer {
                            text-align: center;
                            margin-top: 20px;
                            color: #888;
                        }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <img src='../../../images/trans_logo.png' alt='Smart Foody Logo' class='logo'>
                            <h1>Bienvenue sur Smart Foody !</h1>
                        </div>
                        <p class='content'>
                            Bonjour, $prenom ! Nous sommes ravis de vous accueillir dans notre communauté.
                        </p>
                        <p class='content'>
                            Découvrez nos dernières offres et produits sur notre site web.
                        </p>
                        <p class='content'>
                            Visitez également notre page Facebook pour plus d'informations : <a href='https://www.facebook.com/smartfoody.tn' style='color: green;'>Smart Foody</a>
                        </p>
                        <p class='footer'>
                            Merci de nous avoir rejoints !
                        </p>
                    </div>
                </body>
            </html>
        ");

        $this->mailer->send($email);
    }
}
