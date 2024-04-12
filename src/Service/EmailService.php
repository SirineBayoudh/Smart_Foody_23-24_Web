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

    public function sendResetPasswordEmail(string $to, string $subject, string $prenom)
    {

        $email = (new Email())
            ->from('smartfoody.2024@gmail.com')
            ->to($to)
            ->subject($subject)
            ->html("
                <html>
                    <body>
                        <p style='color: #000;font-size:18px;padding-left: 20px;font-weight:bold'>Bonjour, $prenom</p>
                        <div style='width: 500px;padding: 20px;'>
                            <h2 style='text-transform: uppercase; color: #000;'>VOUS AVEZ DEMANDÉ LA MODIFICATION DE VOTRE MOT DE PASSE</h2>
                            <p style='color: #000;font-size:14px;'> Veuillez copier le code suivant :</p>
                            <p style='color: #000;font-size:14px;'>Visitez notre page Facebook pour plus d'informations : <a href='https://www.facebook.com/smartfoody.tn' style='color: green;'>Smart Foody</a></p>
                        </div>
                    </body>
                </html>
            ");

        $this->mailer->send($email);
    }
}
