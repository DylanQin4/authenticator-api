<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
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
        UserRepository $userRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $errors = [];

        if ($userRepository->findOneBy(['email' => $data['email']])) {
            $errors['email'] = 'Cet email est déjà utilisé.';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Le mot de passe ne peut pas être vide.';
        } elseif (strlen($data['password']) < 3) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 3 caractères.';
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
        $user->setRoles(['ROLE_USER']);

        $validationErrors = $validator->validate($user);
        if (count($validationErrors) > 0) {
            foreach ($validationErrors as $error) {
                $property = $error->getPropertyPath(); // Nom de l'attribut ayant l'erreur
                $errors[$property] = $error->getMessage();
            }
        }

        if (!empty($errors)) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Inscription réussie'
        ], Response::HTTP_CREATED);
    }
}
