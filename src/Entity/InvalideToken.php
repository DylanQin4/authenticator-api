<?php

namespace App\Entity;

use App\Repository\InvalideTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvalideTokenRepository::class)]
class InvalideToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $tokenId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTokenId(): ?int
    {
        return $this->tokenId;
    }

    public function setTokenId(int $tokenId): static
    {
        $this->tokenId = $tokenId;

        return $this;
    }
}
