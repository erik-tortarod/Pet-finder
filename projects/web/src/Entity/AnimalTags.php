<?php

namespace App\Entity;

use App\Repository\AnimalTagsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnimalTagsRepository::class)]
#[ORM\Index(name: 'idx_animal_id', fields: ['animalId'])]
#[ORM\Index(name: 'idx_tag_id', fields: ['tagId'])]
class AnimalTags
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'animalTags')]
    private ?Animals $animalId = null;

    #[ORM\ManyToOne(inversedBy: 'animalTags')]
    private ?Tags $tagId = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

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

    public function getTagId(): ?Tags
    {
        return $this->tagId;
    }

    public function setTagId(?Tags $tagId): static
    {
        $this->tagId = $tagId;

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
}
