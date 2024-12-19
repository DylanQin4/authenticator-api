<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(string $to, string $subject, string $htmlContent, string $imagePath): void
    {
        $email = (new Email())
            ->from('sitrakaitu@gmail.com') // Your Gmail address
            ->to($to)
            ->subject($subject)
            ->html($htmlContent);

        // Attach the image as inline
        if (file_exists($imagePath)) {
            $email->embed(fopen($imagePath, 'r'), 'image1');
        }

        $this->mailer->send($email);
    }
}
