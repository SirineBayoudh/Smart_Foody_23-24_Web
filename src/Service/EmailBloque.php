<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailBloque
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendLockoutEmail(string $to, string $subject, string $prenom, int $userId)
    {
        $url = "http://127.0.0.1:8000/reactivate?userId=$userId";

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
                            <a target='_blank' href='https://viewstripo.email'>
                                <img src='https://eetnmyy.stripocdn.email/content/guids/CABINET_02d1bc47a643a3e7bfe02b0f41d6cb58a6c2703f13c0ecd11cddd42b47af504e/images/image.png' alt='Logo' height='80' title='Logo' class='adapt-img'>
                            </a>
                        </div>
                        <p class='content'>
                            Bonjour, $prenom ! Votre compte a été verrouillé après trois tentatives de connexion infructueuses.
                        </p>
                        <p class='content'>
                        Veuillez cliquer sur <a href='$url'>ce lien</a> pour réactiver votre compte.
                        </p>
                    </div>
                </body>
            </html>
        ");

        $this->mailer->send($email);
    }
}
