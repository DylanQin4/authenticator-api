<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Veuillez entrer votre Nom')]
    #[Assert\Length(max: 250, maxMessage: 'Votre nom est invalide')]
    private ?string $lastName = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Veuillez renseigner un email.')]
    #[Assert\Email(message: 'Veuillez renseigner un email valide.')]
    #[Assert\Length(max: 255, maxMessage: 'Votre email est invalide')]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez renseigner un mot de passe.')]
    #[Assert\Length(min: 6, minMessage: 'Le mot de passe doit contenir au moins 6 caractères.')]
    #[Assert\Regex(
        pattern: "/[A-Z]/",
        message: "Le mot de passe doit contenir au moins une lettre majuscule."
    )]
    #[Assert\Regex(
        pattern: "/[a-z]/",
        message: "Le mot de passe doit contenir au moins une lettre minuscule."
    )]
    #[Assert\Regex(
        pattern: "/[0-9]/",
        message: "Le mot de passe doit contenir au moins un chiffre."
    )]
    private ?string $password = null;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $loginAttempts = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isVerified = false;

    public function incrementsLoginAttempts(): void
    {
        $this->setLoginAttempts($this->getLoginAttempts() + 1);
    }

    public function resetLoginAttempts(): void
    {
        $this->setLoginAttempts(0);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getLoginAttempts(): ?int
    {
        return $this->loginAttempts;
    }

    public function setLoginAttempts(int $loginAttempts): static
    {
        $this->loginAttempts = $loginAttempts;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function isEmailVerified(): bool
    {
        return $this->isVerified;
    }
}
