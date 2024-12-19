<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\PinService;
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
        PinService $pinService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        $user = $userRepository->findOneBy(['email' => $email]);
        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Email ou mot de passe incorrect.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($user->getLoginAttempts() < 3) {
            $pin = $pinService->generatePin();
            $user->setPin($pin);
            $this->getDoctrine()->getManager()->flush();

            return new JsonResponse([
                'status' => 'error',
                'message' => 'Vous avez dépassé le nombre de tentatives de connexion autorisées. Un code PIN a été envoyé à votre adresse email.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse(['message' => 'Authentication successful'], Response::HTTP_OK);
    }
}
