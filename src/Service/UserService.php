<?php

namespace App\Service;

use App\Entity\Token;
use App\Entity\User;
use App\Repository\PinRepository;
use App\Repository\TokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;

class UserService {
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createUser(User $user) {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
    public function updateUser(User $user) {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}