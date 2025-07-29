<?php

namespace App\Entity;

use App\Repository\FoundAnimalsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FoundAnimalsRepository::class)]
#[ORM\Index(name: 'idx_animal_id', fields: ['animalId'])]
#[ORM\Index(name: 'idx_user_id', fields: ['userId'])]
#[ORM\Index(name: 'idx_found_date', fields: ['foundDate'])]
#[ORM\Index(name: 'idx_found_zone', fields: ['foundZone'])]
#[ORM\Index(name: 'idx_created_at', fields: ['createdAt'])]
class FoundAnimals
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'foundAnimals', cascade: ['persist', 'remove'])]
    private ?Animals $animalId = null;

    #[ORM\ManyToOne(inversedBy: 'foundAnimals')]
    private ?User $userId = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $foundDate = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $foundTime = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $foundZone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $foundAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $foundCircumstances = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $additionalNotes = null;

    #[ORM\Column(nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $longitude = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnimalId(): ?Animals
    {
        return $this->animalId;
    }

    public function setAnimalId(?Animals $animalId): static
    {
        $this->animalId = $animalId;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getFoundDate(): ?\DateTime
    {
        return $this->foundDate;
    }

    public function setFoundDate(\DateTime $foundDate): static
    {
        $this->foundDate = $foundDate;

        return $this;
    }

    public function getFoundTime(): ?\DateTime
    {
        return $this->foundTime;
    }

    public function setFoundTime(?\DateTime $foundTime): static
    {
        $this->foundTime = $foundTime;

        return $this;
    }

    public function getFoundZone(): ?string
    {
        return $this->foundZone;
    }

    public function setFoundZone(?string $foundZone): static
    {
        $this->foundZone = $foundZone;

        return $this;
    }

    public function getFoundAddress(): ?string
    {
        return $this->foundAddress;
    }

    public function setFoundAddress(?string $foundAddress): static
    {
        $this->foundAddress = $foundAddress;

        return $this;
    }

    public function getFoundCircumstances(): ?string
    {
        return $this->foundCircumstances;
    }

    public function setFoundCircumstances(?string $foundCircumstances): static
    {
        $this->foundCircumstances = $foundCircumstances;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getAdditionalNotes(): ?string
    {
        return $this->additionalNotes;
    }

    public function setAdditionalNotes(?string $additionalNotes): static
    {
        $this->additionalNotes = $additionalNotes;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }
}
