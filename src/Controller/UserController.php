<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\EmailService;
use App\Service\TokenService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController {


    #[Route('/api/password/reset/{token}', name: 'password_reset', methods: ['POST'])]
    public function reset(Request $request,TokenService $tokenService,UserService $userService,string $token): JsonResponse {
      $data = json_decode($request->getContent(), true);
      if (!$token) {
        return new JsonResponse([
            'status' => 'error',
            'message' => 'Token manquant.'
        ], Response::HTTP_BAD_REQUEST);
      }
      //$token = $data['token'];
      try {
        $tokenEnt= $tokenService->getToken($token);
        if ($tokenEnt->isExpired()) {
          return new JsonResponse([
            'status' => 'error',
            'message' => 'Token expiré.'
        ], Response::HTTP_BAD_REQUEST);
        }
        $user= $tokenEnt->getUser();
        $oldpass=$data['password'];
        if($user->getPassword()!=$oldpass){
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Mot de passe incorrect.'
            ], Response::HTTP_UNAUTHORIZED);
        }
        $user->setPassword($data['new_password']);
        $userService->updateUser($user);
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
  
    #[Route('/api/user/update', name: 'update_user', methods: ['PUT'])]
    public function updateUser(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['token'])) {
            return new JsonResponse(['error' => 'Token manquant.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié.'], JsonResponse::HTTP_UNAUTHORIZED);
        }
        if (isset($data['firstname'])) {
            $user->setFirstName($data['firstname']);
        }

        if (isset($data['lastname'])) {
            $user->setLastName($data['lastname']);
        }

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Informations utilisateur mises à jour avec succès.'], JsonResponse::HTTP_OK);
    }
}
