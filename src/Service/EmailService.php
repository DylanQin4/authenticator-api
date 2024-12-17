<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;  // Importation de l'entité User

class EmailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Envoie un email avec un contenu spécifié.
     *
     * @param string $content Le contenu de l'email
     * @param string $subject Le sujet de l'email
     * @param string $to L'adresse email du destinataire
     * @param UserInterface $user L'utilisateur qui envoie l'email, pour récupérer ses rôles
     * 
     * @return bool True si l'email a été envoyé avec succès, sinon false
     */
    public function sendEmail(string $content, string $subject, string $to, UserInterface $user): bool
    {
        try {
            // Récupérer les rôles de l'utilisateur
            $roles = $user->getRoles();
            $roleString = implode(', ', $roles);  // Convertir les rôles en chaîne de caractères

            // Créer l'email
            $email = (new Email())
                ->from('volatiananna@example.com')  // L'adresse de l'expéditeur
                ->to(new Address($to))           // L'adresse du destinataire
                ->subject($subject)              // Le sujet
                ->text($content)                 // Le contenu en texte
                ->html('<p>' . $content . '</p><p><strong>Roles:</strong> ' . $roleString . '</p>');  // Ajouter les rôles dans le message HTML

            // Envoyer l'email
            $this->mailer->send($email);

            return true;
        } catch (\Exception $e) {
            // Si une erreur survient lors de l'envoi de l'email
            return false;
        }
    }
}
