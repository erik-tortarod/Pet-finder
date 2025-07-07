<?php

namespace App\Entity;

use App\Repository\AnimalsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnimalsRepository::class)]
#[ORM\Index(name: 'idx_animal_type', fields: ['animalType'])]
#[ORM\Index(name: 'idx_animal_gender', fields: ['gender'])]
#[ORM\Index(name: 'idx_animal_size', fields: ['size'])]
#[ORM\Index(name: 'idx_animal_created_at', fields: ['createdAt'])]
#[ORM\Index(name: 'idx_animal_updated_at', fields: ['updatedAt'])]
#[ORM\Index(name: 'idx_animal_color', fields: ['color'])]
class Animals
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $animalType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $age = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $color = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'gender_enum')]
    private ?string $gender = null;

    #[ORM\Column(type: 'size_enum')]
    private ?string $size = null;

    #[ORM\OneToOne(mappedBy: 'animalId', cascade: ['persist', 'remove'])]
    private ?LostPets $lostPets = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAnimalType(): ?string
    {
        return $this->animalType;
    }

    public function setAnimalType(string $animalType): static
    {
        $this->animalType = $animalType;

        return $this;
    }

    public function getAge(): ?string
    {
        return $this->age;
    }

    public function setAge(?string $age): static
    {
        $this->age = $age;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(string $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getLostPets(): ?LostPets
    {
        return $this->lostPets;
    }

    public function setLostPets(?LostPets $lostPets): static
    {
        // unset the owning side of the relation if necessary
        if ($lostPets === null && $this->lostPets !== null) {
            $this->lostPets->setAnimalId(null);
        }

        // set the owning side of the relation if necessary
        if ($lostPets !== null && $lostPets->getAnimalId() !== $this) {
            $lostPets->setAnimalId($this);
        }

        $this->lostPets = $lostPets;

        return $this;
    }
}
