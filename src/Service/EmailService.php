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

    public function sendEmail(string $to, string $subject, string $htmlContent, ?string $imagePath = null): void
    {
        $email = (new Email())
            ->from('sitrakaitu@gmail.com')
            ->to($to)
            ->subject($subject)
            ->html($htmlContent);

        // Attach the image as inline if the path is provided
        if ($imagePath !== null && file_exists($imagePath)) {
            $email->embed(fopen($imagePath, 'r'), 'image1');
        }

        $this->mailer->send($email);
    }
}
