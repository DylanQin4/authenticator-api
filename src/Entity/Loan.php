<?php

namespace App\Entity;

use App\Repository\LoanRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LoanRepository::class)]
class Loan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $loanDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $returnDate = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Book $book = null;

//    #[ORM\ManyToOne(inversedBy: 'loans')]
//    #[ORM\JoinColumn(nullable: false)]
//    private ?OldUser $loanedBy = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLoanDate(): ?\DateTimeInterface
    {
        return $this->loanDate;
    }

    public function setLoanDate(\DateTimeInterface $loanDate): static
    {
        $this->loanDate = $loanDate;

        return $this;
    }

    public function getReturnDate(): ?\DateTimeInterface
    {
        return $this->returnDate;
    }

    public function setReturnDate(?\DateTimeInterface $returnDate): static
    {
        $this->returnDate = $returnDate;

        return $this;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): static
    {
        $this->book = $book;

        return $this;
    }

//    public function getLoanedBy(): ?OldUser
//    {
//        return $this->loanedBy;
//    }

//    public function setLoanedBy(?OldUser $loanedBy): static
//    {
//        $this->loanedBy = $loanedBy;
//
//        return $this;
//    }
}
