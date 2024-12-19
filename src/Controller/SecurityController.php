<?php

namespace App\Controller;

use App\Entity\Token;
use App\Repository\PinRepository;
use App\Repository\UserRepository;
use App\Service\PinService;
use App\Service\TokenService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    #[Route('/api/login_check', name: 'app_login_check', methods: ['POST'])]
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        TokenService $tokenService,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        $user = $userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Cet email n\'est associe a aucun compte.'
            ], Response::HTTP_UNAUTHORIZED);
        }
        if (!$passwordHasher->isPasswordValid($user, $password)) {
            $user->setLoginAttempts($user->getLoginAttempts() + 1);
            $entityManager->persist($user);
            $entityManager->flush();

            if ($user->getLoginAttempts() >= 3) {
                try {
                    $tokenService->createAndSaveToken($user, new \DateTimeImmutable('+1 hour'));
                } catch (\Exception $e) {
                    return new JsonResponse([
                        'status' => 'error',
                        'message' => 'Une erreur est survenue lors de la creation du token de validation.'
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
//            url=api/reset-attempts{token}
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Vous avez dépassé le nombre de tentatives de connexion autorisées. Un email de reinitialisation du tentative vous a été envoyé.',
                ], Response::HTTP_UNAUTHORIZED);
            } else {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Mot de passe incorrect.'
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        $user->setLoginAttempts(0);
        try {
            $accessToken = $tokenService->getAccessToken($user, $entityManager)->getToken();
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la creation du token d\'authentification.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $entityManager->persist($user);
        $entityManager->flush();
        return new JsonResponse([
            'status' => 'success',
            'access-token' => $accessToken,
            'message' => 'Authentication successful'
        ], Response::HTTP_OK);
    }

    #[Route('/api/validate-pin/{pin}', name: 'api_validate_pin', methods: ['GET'])]
    public function validatePin(
        string $pin,
        PinRepository $pinRepository,
        EntityManagerInterface $entityManager,
        TokenService $tokenService
    ): JsonResponse {
        $pinEntity = $pinRepository->findOneBy(['codePin' => $pin]);

        if (!$pinEntity) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Code PIN invalide.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($pinEntity->getExpiredAt() < new \DateTimeImmutable()) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Code PIN expiré.'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $tokenValue = $tokenService->generateValidationToken();
            $user = $pinEntity->getUser();
            // reinitialiser le nombre de tentative de connexion
            $user->setLoginAttempts(0);

            $token = new Token();
            $token->setToken($tokenValue);
            $token->setExpiredAt((new \DateTimeImmutable())->modify('+24 hour'));
            $token->setUser($user);

            $entityManager->persist($token);
            $entityManager->remove($pinEntity);
            $entityManager->persist($user);
            $entityManager->flush();

//            $this->mailer->sendEmail($user->getEmail(), $tokenValue);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de l\'inscription.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Code PIN validé avec succès.'
        ]);
    }

}
