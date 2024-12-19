<?php
namespace App\Controller;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MagicLinkController extends AbstractController
{
    private static string $SECRET_KEY; // Static property
    private const TOKEN_EXPIRATION = 900; // (90 seconds)

    public function __construct()
    {
        // Access environment variable at runtime
        self::$SECRET_KEY = $_ENV['JWT_SECRET_KEY'] ?? 'default_secret_key'; // Fallback to default if not set
    }

    #[Route('/auth/register', name: 'register_magic_link', methods: ['POST'])]
    public function register(Request $request, MailerInterface $mailer): JsonResponse
    {
        // Get user email from request
        $data = json_decode($request->getContent(), true);
        $userEmail = $data['email'] ?? null;

        if (!$userEmail || !filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['error' => 'Invalid email'], Response::HTTP_BAD_REQUEST);
        }

        // Generate JWT Token
        $payload = [
            'email' => $userEmail,
            'exp' => time() + self::TOKEN_EXPIRATION, // Token expiration time
        ];
        $token = JWT::encode($payload, self::$SECRET_KEY, 'HS256');

        // Generate Magic Link
        $magicLink = $this->generateUrl('verify_magic_link', ['token' => $token], true);

        // Send Magic Link via Email
        $email = (new Email())
            ->from('no-reply@yourapp.com')
            ->to($userEmail)
            ->subject('Link to confirm registration')
            ->html(<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Registration</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(120deg, #6a11cb, #2575fc);
            color: #fff;
        }
        .container {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 90%;
        }
        h1 {
            font-size: 2em;
            margin-bottom: 20px;
        }
        .confirm-button {
            display: inline-block;
            background-color: #28a745;
            color: #fff;
            padding: 10px 20px;
            font-size: 1em;
            text-transform: uppercase;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .confirm-button:hover {
            background-color: #218838;
        }
        .confirm-button:active {
            transform: scale(0.95);
        }
        .backup-link {
            display: block;
            margin-top: 20px;
            font-size: 0.9em;
            color: #ddd;
        }
        .backup-link a {
            color: #f9d423;
            text-decoration: none;
        }
        .backup-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Confirm Your Registration</h1>
        <a href="http://localhost:8000{$magicLink}" class="confirm-button">Confirm Register</a>
        <p class="backup-link">If the button doesn't work, <a href="http://localhost:8000{$magicLink}">click here to confirm</a>.</p>
        <p>you can use direct link instead localhost:8000{$magicLink}</p>
    </div>
</body>
</html>
HTML);

        $mailer->send($email);

        return $this->json(['message' => 'Magic link sent to your email!']);
    }

    #[Route('/auth/verify', name: 'verify_magic_link', methods: ['GET'])]
    public function verify(Request $request): JsonResponse
    {
        $token = $request->query->get('token');

        if (!$token) {
            return $this->json(['error' => 'Token is missing'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Decode and validate the JWT token
            $decoded = JWT::decode($token, new Key(self::$SECRET_KEY, 'HS256'));
            return $this->json(['message' => 'Access granted!', 'user' => $decoded->email]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid or expired token'], Response::HTTP_UNAUTHORIZED);
        }
    }
}
