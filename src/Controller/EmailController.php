<?php

namespace App\Controller;

use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Service\TokenService;

class EmailController extends AbstractController
{
    private EmailService $emailService;
    private TokenService $tokenService;

    public function __construct(EmailService $emailService, TokenService $tokenService)
    {
        $this->emailService = $emailService;
        $this->tokenService = $tokenService;
    }

    #[Route('/api/send-email', name: 'send_email', methods: ['POST'])]
    public function sendEmail(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate input
        if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Invalid email address'], Response::HTTP_BAD_REQUEST);
        }

        $recipient = $data['email'];
        $subject = "HTML @ mail oh !!!";
        $htmlContent = "
            <html>
              <head>
                <style>
                  body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
                  h1 { color: #333; text-align: center; }
                  img { text-align: center; }
                  .content { margin: 0 auto; width: 80%; background-color: #fff; padding: 20px; border-radius: 8px; }
                  .footer { text-align: center; font-size: 12px; color: #888; }
                </style>
              </head>
              <body>
                <div class='content'>
                  <h1>Ito le email Test Misy css</h1>
                  <p>Mandefa html misy css sy sary </p>
                  <img src='cid:image1' />
                </div>
                <div class='footer'>
                  <p>Sent with ❤️ by smart</p>
                </div>
              </body>
            </html>";

        $imagePath = __DIR__ . '/../../public/360.jpg'; // Adjust the path to your image

        try {
            $this->emailService->sendEmail($recipient, $subject, $htmlContent, $imagePath);
            return new JsonResponse(['message' => 'Email sent successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/api/send-email-token', name: 'send_token', methods: ['POST'])]
    public function sendToken(Request $request): JsonResponse {
      $data = json_decode($request->getContent(), true);
      if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return new JsonResponse(['error' => 'Invalid email address'], Response::HTTP_BAD_REQUEST);
      }
      $url= "/api/register/";
      $token= $this->tokenService->generateValidationToken();
      $recipient = $data['email'];
      $subject = "Confirmation Token";
      $htmlContent = $this->emailService->generateHtmlValidationToken($url,$token);

      try {
        $this->emailService->sendEmail($recipient, $subject, $htmlContent);
        return new JsonResponse(['message' => 'Email sent successfully'], Response::HTTP_OK);
      } catch (\Exception $e) {
          return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
      }
    }
}
