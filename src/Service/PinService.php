<?php

namespace App\Service;

use App\Entity\Pin;
use App\Repository\PinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use \DateTimeImmutable;

class PinService
{
    private $entityManager;
    private $pinRepository;

    public function __construct(EntityManagerInterface $entityManager, PinRepository $pinRepository)
    {
        $this->entityManager = $entityManager;
        $this->pinRepository = $pinRepository;
    }

    /**
     * Génère un code PIN et l'associe à une date d'expiration.
     * Par défaut, la date d'expiration est de 90 secondes à partir de la génération.
     *
     * @param int $expirationDuration Durée d'expiration en secondes (par défaut 90)
     * @return Pin Le PIN généré
     */
    public function generatePin(int $expirationDuration = 90): Pin
    {
        // Générer un code PIN aléatoire
        $pinCode = $this->generateRandomPin();

        // Créer l'entité Pin
        $pin = new Pin();
        $pin->setCodePin($pinCode);

        // Définir la date d'expiration
        $expirationDate = new DateTimeImmutable("+{$expirationDuration} seconds");
        $pin->setExpiredAt($expirationDate);

        // Sauvegarder le PIN dans la base de données
        $this->entityManager->persist($pin);
        $this->entityManager->flush();

        return $pin;
    }

    public function validatePin(string $pin): bool
    {
        $pinEntity = $this->pinRepository->findOneBy(['codePin' => $pin]);

        if (!$pinEntity) {
            return false;
        }

        $now = new DateTimeImmutable();
        if ($now > $pinEntity->getExpiredAt()) {
            return false;
        }

        $this->entityManager->remove($pinEntity);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Génère un code PIN aléatoire de 6 chiffres.
     *
     * @return string Le code PIN généré
     */
    private function generateRandomPin(): string
    {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT); // Génère un code PIN à 6 chiffres
    }
}
