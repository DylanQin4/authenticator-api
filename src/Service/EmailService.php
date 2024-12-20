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
    public function generateHtmlValidationToken(string $url,string $token):string {
        $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    $safeToken = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');

    // Construct HTML
    $html = '<!DOCTYPE html>';
    $html .= '<html lang="en">';
    $html .= '<head>';
    $html .= '<meta charset="UTF-8">';
    $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    $html .= '<title>Confirm Registration</title>';
    $html .= '<style>';
    $html .= 'body { font-family: Arial, sans-serif; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; background: linear-gradient(120deg, #6a11cb, #2575fc); color: #fff; }';
    $html .= '.container { text-align: center; background: rgba(255, 255, 255, 0.1); padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); max-width: 400px; width: 90%; }';
    $html .= 'h1 { font-size: 2em; margin-bottom: 20px; }';
    $html .= '.confirm-button { display: inline-block; background-color: #28a745; color: #fff; padding: 10px 20px; font-size: 1em; text-transform: uppercase; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); transition: background-color 0.3s ease, transform 0.2s ease; }';
    $html .= '.confirm-button:hover { background-color: #218838; }';
    $html .= '.confirm-button:active { transform: scale(0.95); }';
    $html .= '.backup-link { display: block; margin-top: 20px; font-size: 0.9em; color: #ddd; }';
    $html .= '.backup-link a { color: #f9d423; text-decoration: none; }';
    $html .= '.backup-link a:hover { text-decoration: underline; }';
    $html .= '.input-group { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }';
    $html .= 'input[type="text"] { padding: 10px; font-size: 16px; border: 1px solid #ccc; border-radius: 4px; flex: 1; outline: none; }';
    $html .= 'button { padding: 10px 20px; font-size: 16px; color: #fff; background-color: #007bff; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.3s; }';
    $html .= 'button:hover { background-color: #0056b3; }';
    $html .= '.message { font-size: 14px; color: #28a745; display: none; }';
    $html .= '</style>';
    $html .= '</head>';
    $html .= '<body>';
    $html .= '<div class="container">';
    $html .= '<h1>Kely sisa</h1>';
    $html .= '<a href="http://127.0.0.1:8000' . $safeUrl . $safeToken . '" class="confirm-button">Manaiky ny Hiditra</a>';
    $html .= '<p class="backup-link">Raha tsy mandeha, <a href="http://127.0.0.1:8000' . $safeUrl . $safeToken . '">dia ity hidirana azafady</a>.</p>';
    $html .= '<p>Afaka mampiasa ity rohy ity mivantana koa: <a href="http://127.0.0.1:8000' . $safeUrl . $safeToken . '">http://127.0.0.1:8000' . $safeUrl . $safeToken . '</a></p>';
    $html .= '</body>';
    $html .= '</html>'; 
        return $html;
    }
    public function generateHtmlValidationPin(string $pin): string{
        $safePin = htmlspecialchars($pin, ENT_QUOTES, 'UTF-8');

    // Construct HTML
    $html = '<!DOCTYPE html>';
    $html .= '<html lang="en">';
    $html .= '<head>';
    $html .= '<meta charset="UTF-8">';
    $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    $html .= '<title>PIN for Authentication</title>';
    $html .= '<style>';
    $html .= 'h2 { font-family: Arial, sans-serif; font-size: 2em; color: #4CAF50; text-align: center; background-color: #f0f0f0; padding: 20px; border-radius: 8px; width: 50%; margin: 50px auto; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }';
    $html .= '.pin { font-family: "Courier New", Courier, monospace; font-size: 2em; color: #FF5722; background-color: #fff; padding: 5px 16px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); letter-spacing: 3px; }';
    $html .= '</style>';
    $html .= '</head>';
    $html .= '<body>';
    $html .= '<h2>Authentication PIN: <span class="pin">' . $safePin . '</span></h2>';
    $html .= '</body>';
    $html .= '</html>';
        return $html;
    }

    public function generateHtmlForgot(string $url,string $token):string {
        $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    $safeToken = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');

    // Construct HTML
    $html = '<!DOCTYPE html>';
    $html .= '<html lang="en">';
    $html .= '<head>';
    $html .= '<meta charset="UTF-8">';
    $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    $html .= '<title>Confirm Registration</title>';
    $html .= '<style>';
    $html .= 'body { font-family: Arial, sans-serif; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; background: linear-gradient(120deg, #6a11cb, #2575fc); color: #fff; }';
    $html .= '.container { text-align: center; background: rgba(255, 255, 255, 0.1); padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); max-width: 400px; width: 90%; }';
    $html .= 'h1 { font-size: 2em; margin-bottom: 20px; }';
    $html .= '.confirm-button { display: inline-block; background-color: #28a745; color: #fff; padding: 10px 20px; font-size: 1em; text-transform: uppercase; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); transition: background-color 0.3s ease, transform 0.2s ease; }';
    $html .= '.confirm-button:hover { background-color: #218838; }';
    $html .= '.confirm-button:active { transform: scale(0.95); }';
    $html .= '.backup-link { display: block; margin-top: 20px; font-size: 0.9em; color: #ddd; }';
    $html .= '.backup-link a { color: #f9d423; text-decoration: none; }';
    $html .= '.backup-link a:hover { text-decoration: underline; }';
    $html .= '.input-group { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }';
    $html .= 'input[type="text"] { padding: 10px; font-size: 16px; border: 1px solid #ccc; border-radius: 4px; flex: 1; outline: none; }';
    $html .= 'button { padding: 10px 20px; font-size: 16px; color: #fff; background-color: #007bff; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.3s; }';
    $html .= 'button:hover { background-color: #0056b3; }';
    $html .= '.message { font-size: 14px; color: #28a745; display: none; }';
    $html .= '</style>';
    $html .= '</head>';
    $html .= '<body>';
    $html .= '<div class="container">';
    $html .= '<h1>Mot de passe</h1>';
    $html .= '<a href="http://127.0.0.1:8000' . $safeUrl . $safeToken . '" class="confirm-button">mot de passe oublié</a>';
    $html .= '<p class="backup-link">Raha tsy mandeha, <a href="http://127.0.0.1:8000' . $safeUrl . $safeToken . '">dia ity hidirana azafady</a>.</p>';
    $html .= '<p>Afaka mampiasa ity rohy ity mivantana koa: <a href="http://127.0.0.1:8000' . $safeUrl . $safeToken . '">http://127.0.0.1:8000' . $safeUrl . $safeToken . '</a></p>';
    $html .= '</body>';
    $html .= '</html>'; 
        return $html;
    }
}
