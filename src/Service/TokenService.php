<?php
namespace App\Service;

use App\Entity\Token;
use App\Entity\User;
use App\Repository\PinRepository;
use App\Repository\TokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TokenService
{
    private EntityManagerInterface $entityManager;
    private TokenRepository $tokenRepository;

    public function __construct(EntityManagerInterface $entityManager, TokenRepository $tokenRepository)
    {
        $this->entityManager = $entityManager;
        $this->tokenRepository = $tokenRepository;
    }
    /**
     * @throws RandomException
     */
    public function generateValidationToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * @throws \DateMalformedStringException
     * @throws RandomException
     */
    public function createAndSaveToken(User $user, \DateTimeImmutable $expiredAt): Token
    {
        $token = new Token();
        $token->setUser($user);
        $token->setToken($this->generateValidationToken());
        $token->setExpiredAt($expiredAt);

        // Sauvegarder le token
        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token;
    }

    /**
     * @throws \DateMalformedStringException
     * @throws RandomException
     */
    public function getAccessToken(User $user, EntityManagerInterface $entityManager): Token
    {
        $token = $this->tokenRepository->findOneBy(['user' => $user]);
        $entityManager->remove($token);
        $entityManager->flush();

        return $this->createAndSaveToken($user, new \DateTimeImmutable('+1 hour'));
    }
}