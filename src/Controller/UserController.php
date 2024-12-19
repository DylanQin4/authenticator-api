<?php

namespace App\Controller;

use App\Service\EmailService;
use App\Service\TokenService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class UserContrller extends AbstractController {


    #[Route('/api/password/reset', name: 'password_reset', methods: ['POST'])]
    public function reset(Request $request,TokenService $tokenService,UserService $userService): JsonResponse {
      $data = json_decode($request->getContent(), true);
      if (!isset($data['token'])) {
        return new JsonResponse([
            'status' => 'error',
            'message' => 'Token manquant.'
        ], Response::HTTP_BAD_REQUEST);
      }
      $token = $data['token'];
      try {
        $tokenEnt= $tokenService->getToken($token);
        $user= $tokenEnt->getUser();
        $oldpass=$data['password'];
        if($user->getPassword()!=$oldpass){
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Mot de passe incorrect.'
            ], Response::HTTP_UNAUTHORIZED);
        }
        $user->setPassword($data['new_password']);
        $userService->createUser($user);
        return new JsonResponse(['message' => 'Mot de passe réinitialisé avec succès.'], Response::HTTP_OK);
      } catch (\Exception $e)  {
        return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
      }
    }
    #[Route('/api/password/forgot', name: 'password_forgot', methods: ['POST'])]
    public function forgot(Request $request,TokenService $tokenService,EmailService $emailService): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Invalid email address'], Response::HTTP_BAD_REQUEST);
        }
        $url= "/api/password/reset/";
        $token= $tokenService->generateValidationToken();
        $recipient = $data['email'];
        $subject = "Mot de passe";
        $htmlContent = $emailService->generateHtmlForgot($url,$token);

        try {
            $emailService->sendEmail($recipient, $subject, $htmlContent);
            return new JsonResponse(['message' => 'Email sent successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}