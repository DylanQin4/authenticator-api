<?php

namespace App\Service;

use App\Entity\User;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;

class FirebaseService
{
    private Auth $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
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
            'displayName' => $user->getFirstName() . ' ' . $user->getLastName(),
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

}
