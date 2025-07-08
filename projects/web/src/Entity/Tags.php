<?php

namespace App\Entity;

use App\Repository\TagsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagsRepository::class)]
#[ORM\Index(name: 'idx_name', fields: ['name'])]
class Tags
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, AnimalTags>
     */
    #[ORM\OneToMany(targetEntity: AnimalTags::class, mappedBy: 'tagId')]
    private Collection $animalTags;

    public function __construct()
    {
        $this->animalTags = new ArrayCollection();
    }

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

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

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

    /**
     * @return Collection<int, AnimalTags>
     */
    public function getAnimalTags(): Collection
    {
        return $this->animalTags;
    }

    public function addAnimalTag(AnimalTags $animalTag): static
    {
        if (!$this->animalTags->contains($animalTag)) {
            $this->animalTags->add($animalTag);
            $animalTag->setTagId($this);
        }

        return $this;
    }

    public function removeAnimalTag(AnimalTags $animalTag): static
    {
        if ($this->animalTags->removeElement($animalTag)) {
            // set the owning side to null (unless already changed)
            if ($animalTag->getTagId() === $this) {
                $animalTag->setTagId(null);
            }
        }

        return $this;
    }
}
