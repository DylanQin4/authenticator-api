<?php

namespace App\Service;

use App\Entity\InvalideToken;
use App\Entity\Token;
use App\Entity\User;
use App\Repository\PinRepository;
use App\Repository\TokenRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Random\RandomException;

class UserService {
    private EntityManagerInterface $entityManager;
    private FirebaseService $firebaseService;

    public function __construct(EntityManagerInterface $entityManager, FirebaseService $firebaseService)
    {
        $this->entityManager = $entityManager;
        $this->firebaseService = $firebaseService;
    }

    public function getUserByEmail(string $email): ?User {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    public function createUser(User $user, ?Token $token): void {
        $this->entityManager->persist($user);
        if ($token) {
            $this->entityManager->persist($token);
        }
        $this->entityManager->flush();
    }

    public function updateUser(User $user): void {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @throws FirebaseException
     * @throws Exception
     * @throws AuthException
     */
    public function validateUser(User $user, InvalideToken $invalideToken): void {
        $this->entityManager->getConnection()->beginTransaction(); // Début de la transaction

        try {
            $this->entityManager->remove($invalideToken);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Insertion de l'utilisateur dans Firebase
            $this->firebaseService->syncUser($user);

            // Validation de la transaction
            $this->entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            // Annulation de la transaction en cas d'erreur
            $this->entityManager->getConnection()->rollBack();
            throw $e;
        }
    }
}