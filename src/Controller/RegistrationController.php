<?php

namespace App\Controller;

use App\Entity\InvalideToken;
use App\Entity\Token;
use App\Entity\User;
use App\Repository\InvalideTokenRepository;
use App\Repository\UserRepository;
use App\Repository\TokenRepository;
use App\Repository\PinRepository;
use App\Service\TokenService;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        UserRepository $userRepository,
        TokenService $tokenService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $errors = [];

        if ($userRepository->findOneBy(['email' => $data['email']])) {
            $errors['email'] = 'Cet email est déjà utilisé.';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Le mot de passe ne peut pas être vide.';
        }
        if (empty($data['email'])) {
            $errors['email'] = 'L\'email ne peut pas être vide.';
        }
        if (empty($data['lastname'])) {
            $errors['lastname'] = 'Le nom ne peut pas être vide.';
        }

        if (!empty($errors)) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setLastName($data['lastname']);
        $user->setFirstName($data['firstname'] ?? null);
        $user->setLoginAttempts(0);

        $validationErrors = $validator->validate($user);
        if (count($validationErrors) > 0) {
            foreach ($validationErrors as $error) {
                $property = $error->getPropertyPath();
                $errors[$property] = $error->getMessage();
            }
        }

        if (!empty($errors)) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $tokenValue = $tokenService->generateValidationToken();

            $token = new Token();
            $token->setToken($tokenValue);
            $token->setExpiredAt((new \DateTimeImmutable())->modify('+1 hour'));
            $token->setUser($user);

            $entityManager->persist($user);
            $entityManager->persist($token);
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
            'message' => 'Un email de validation vous a été envoyé.'
        ], Response::HTTP_CREATED);
    }

     
    #[Route('/api/validate-email/{token}', name: 'api_validate_email', methods: ['GET'])]
    public function validateEmail(
        string $token,
        TokenRepository $tokenRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $tokenEntity = $tokenRepository->isValidToken($token);
        } catch (Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Token invalide ou expiré.'
            ], Response::HTTP_BAD_REQUEST);
        }
    
        if (!$tokenEntity) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Token invalide ou expiré.'
            ], Response::HTTP_BAD_REQUEST);
        }
    
        $user = $tokenEntity->getUser();
        
        $user->setVerified(true);

        $invalideToken = new InvalideToken();
        $invalideToken->setTokenId($tokenEntity->getId());

        $entityManager->persist($invalideToken);
        $entityManager->persist($user);
        
//        $user->setEmailVerificationToken(null);
        $entityManager->flush();
    
        return new JsonResponse([
            'status' => 'success',
            'message' => 'Votre email a été vérifié avec succès.'
        ]);
    }
    

    #[Route('/api/validate-pin/{pin}', name: 'api_validate_pin', methods: ['GET'])]
    public function validatePin(
        string $pin,
        PinRepository $pinRepository,
        EntityManagerInterface $entityManager
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
    
        return new JsonResponse([
            'status' => 'success',
            'message' => 'Code PIN validé avec succès.'
        ]);
    }
    
}
