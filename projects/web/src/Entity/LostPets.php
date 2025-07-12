<?php

namespace App\Entity;

use App\Repository\LostPetsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LostPetsRepository::class)]
#[ORM\Index(name: 'idx_animal_id', fields: ['animalId'])]
#[ORM\Index(name: 'idx_user_id', fields: ['userId'])]
#[ORM\Index(name: 'idx_lost_zone', fields: ['lostZone'])]
#[ORM\Index(name: 'idx_lost_date', fields: ['lostDate'])]
#[ORM\Index(name: 'idx_created_at', fields: ['createdAt'])]
class LostPets
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'lostPets', cascade: ['persist', 'remove'])]
    private ?Animals $animalId = null;

    #[ORM\ManyToOne(inversedBy: 'lostPets')]
    private ?User $userId = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $lostDate = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $lostTime = null;

    #[ORM\Column(length: 255)]
    private ?string $lostZone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lostAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lostCircumstances = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rewardAmount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rewardDescription = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

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

    public function getLostDate(): ?\DateTime
    {
        return $this->lostDate;
    }

    public function setLostDate(\DateTime $lostDate): static
    {
        $this->lostDate = $lostDate;

        return $this;
    }

    public function getLostTime(): ?\DateTime
    {
        return $this->lostTime;
    }

    public function setLostTime(?\DateTime $lostTime): static
    {
        $this->lostTime = $lostTime;

        return $this;
    }

    public function getLostZone(): ?string
    {
        return $this->lostZone;
    }

    public function setLostZone(string $lostZone): static
    {
        $this->lostZone = $lostZone;

        return $this;
    }

    public function getLostAddress(): ?string
    {
        return $this->lostAddress;
    }

    public function setLostAddress(?string $lostAddress): static
    {
        $this->lostAddress = $lostAddress;

        return $this;
    }

    public function getLostCircumstances(): ?string
    {
        return $this->lostCircumstances;
    }

    public function setLostCircumstances(?string $lostCircumstances): static
    {
        $this->lostCircumstances = $lostCircumstances;

        return $this;
    }

    public function getRewardAmount(): ?string
    {
        return $this->rewardAmount;
    }

    public function setRewardAmount(?string $rewardAmount): static
    {
        $this->rewardAmount = $rewardAmount;

        return $this;
    }

    public function getRewardDescription(): ?string
    {
        return $this->rewardDescription;
    }

    public function setRewardDescription(?string $rewardDescription): static
    {
        $this->rewardDescription = $rewardDescription;

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
}
