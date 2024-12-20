<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Token;
use App\Repository\TokenRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\EmailService;
use App\Service\TokenService;
use App\Service\UserService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController {


    #[Route('/api/password/reset/{token}', name: 'password_reset', methods: ['POST'])]
    public function reset(Request $request,UserService $userService,string $token, 
    TokenRepository $tokenRepository,
    UserPasswordHasherInterface $passwordHasher): JsonResponse {
      $data = json_decode($request->getContent(), true);
      if (!$data || !isset($data['password'], $data['new_password'])) {
        return new JsonResponse([
            'status' => 'error',
            'message' => 'Invalid or missing input data.'
        ], Response::HTTP_BAD_REQUEST);
    }
    $oldpass=$data['password'];
      if (!$token) {
        return new JsonResponse([
            'status' => 'error',
            'message' => 'Token manquant.'
        ], Response::HTTP_BAD_REQUEST);
      }
      //$token = $data['token'];
      try {
        
        
        $tokenEnt=$tokenRepository->isValidToken($token);
        
        if (!$tokenEnt) {
          return new JsonResponse([
            'status' => 'error',
            'message' => 'Token expiré ou invalide.'
        ], Response::HTTP_BAD_REQUEST);
        }
        $user= $tokenEnt->getUser();
        if(!$passwordHasher->isPasswordValid($user, $oldpass)){
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Mot de passe incorrect.'
            ], Response::HTTP_UNAUTHORIZED);
        }
        $user->setPassword($passwordHasher->hashPassword($user, $data['new_password']));
        $userService->updateUser($user);
        return new JsonResponse(['message' => 'Mot de passe réinitialisé avec succès.'], Response::HTTP_OK);
      } catch (\Exception $e)  {
        return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
      }
    }
    #[Route('/api/password/forgot', name: 'password_forgot', methods: ['POST'])]
    public function forgot(Request $request,TokenService $tokenService,EmailService $emailService,
    EntityManagerInterface $entityManager,
    UserRepository $userRepository): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Invalid email address'], Response::HTTP_BAD_REQUEST);
        }
       
        $url= "/api/password/reset/";
        $token= $tokenService->generateValidationToken();
        $tkn = new Token();
        $tkn->setToken($token);
        $tkn->setExpiredAt((new \DateTimeImmutable())->modify('+1 hour'));
        $recipient = $data['email'];
        $subject = "Mot de passe";
        $htmlContent = $emailService->generateHtmlForgot($url,$token);
        $user= $userRepository->getUserByEmail($recipient);
        $tkn->setUser($user);
        $entityManager->persist($user);
            $entityManager->persist($tkn);
            $entityManager->flush();
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
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        TokenRepository $tokenRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $authorizationHeader = $request->headers->get('Authorization');

        if (!$authorizationHeader) {
            return new JsonResponse(['error' => 'Authorization manquant'], 400);
        }

        if (!str_starts_with($authorizationHeader, 'Bearer ')) {
            return new JsonResponse(['error' => 'Invalid Authorization header format'], 400);
        }
        $token = trim(str_replace('Bearer ', '', $authorizationHeader));
        $tkn =$tokenRepository->isValidToken($token);
        if (!$tkn) {
           return new JsonResponse(['error' => 'Token invalide ou expiré.'], Response::HTTP_UNAUTHORIZED);
        }
        $user = $tkn->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié.'], Response::HTTP_UNAUTHORIZED);
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
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Informations utilisateur mises à jour avec succès.'], Response::HTTP_OK);
    }
}
