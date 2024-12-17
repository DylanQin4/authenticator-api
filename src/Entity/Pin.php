<?php

namespace App\Entity;

use App\Repository\PinRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PinRepository::class)]
class Pin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $codePin = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $expiratedAt = null;

    #[ORM\Column]
    private ?int $userId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodePin(): ?string
    {
        return $this->codePin;
    }

    public function setCodePin(string $codePin): static
    {
        $this->codePin = $codePin;

        return $this;
    }

    public function getExpiratedAt(): ?\DateTimeImmutable
    {
        return $this->expiratedAt;
    }

    public function setExpiratedAt(\DateTimeImmutable $expiratedAt): static
    {
        $this->expiratedAt = $expiratedAt;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }
}