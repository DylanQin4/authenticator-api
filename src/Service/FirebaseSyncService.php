<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class FirebaseSyncService
{
    private Auth $auth;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(Auth $auth, EntityManagerInterface $entityManager, LoggerInterface $logger, UserPasswordHasherInterface $passwordHasher)
    {
        $this->auth = $auth;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @throws FirebaseException
     * @throws AuthException
     */
    public function syncUser(User $user): void
    {
        $properties = [
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'displayName' => $user->getFirstName() . ',' . $user->getLastName(), // Nom complet séparé par une virgule
        ];

        try {
            $existingUser = $this->auth->getUserByEmail($user->getEmail());
            // Met a jour si l'utilisateur existe
            $this->auth->updateUser($existingUser->uid, $properties);
        } catch (UserNotFound $e) {
            // Cree un nouvel utilisateur si aucun utilisateur correspondant n'existe
            $this->auth->createUser($properties);
        }
    }

    /**
     * Synchronise tous les utilisateurs Firebase Auth avec la base locale.
     */
    public function syncUsers(): void
    {
        $users = $this->auth->listUsers(); // recupere tous les utilisateurs Firebase

        foreach ($users as $firebaseUser) {
            $email = $firebaseUser->email;
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$existingUser) {
                $user = new User();
                $user->setEmail($email);
                $user->setPassword($this->passwordHasher->hashPassword($user, 'password')); // mot de passe temporaire

                // Separation du displayName en firstName et lastName par la virgule
                $nameParts = explode(',', $firebaseUser->displayName ?? '');

                if (count($nameParts) === 2) {
                    $user->setFirstName(trim($nameParts[0]));
                    $user->setLastName(trim($nameParts[1]));
                } else {
                    // Si la séparation échoue, utilise une valeur par défaut
                    $user->setFirstName(null);
                    $user->setLastName('Utilisateur firebase');
                }

                $user->setFirebase(true);
                $user->setVerified(true);

                $this->entityManager->persist($user);
                $this->logger->info("Utilisateur ajouté depuis Firebase : " . $email);
            }
        }

        $this->entityManager->flush();
    }

    public function verifyPassword(string $email, string $password): bool
    {
        try {
            $user = $this->auth->getUserByEmail($email);
            $firebaseSignIn = $this->auth->signInWithEmailAndPassword($email, $password);

            return $firebaseSignIn !== null;
        } catch (\Exception $e) {
            return false;
        }
    }


}
