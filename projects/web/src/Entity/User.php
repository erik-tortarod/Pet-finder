<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\Index(name: 'IDX_USER_EMAIL', fields: ['email'])]
#[ORM\Index(name: 'IDX_USER_CREATED_AT', fields: ['createdAt'])]
#[ORM\Index(name: 'IDX_USER_IS_SHELTER', fields: ['isShelter'])]
#[ORM\Index(name: 'IDX_USER_SHELTER_VERIFICATION_STATUS', fields: ['shelterVerificationStatus'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column]
    private ?bool $emailNotifications = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $lastLogin = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column]
    private ?bool $isShelter = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shelterName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shelterDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shelterAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shelterPhone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shelterWebsite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shelterFacebook = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shelterVerificationStatus = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $shelterVerificationDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $resetTokenExpiresAt = null;

    #[ORM\Column]
    private ?bool $resetTokenUsed = false;

    /**
     * @var Collection<int, LostPets>
     */
    #[ORM\OneToMany(targetEntity: LostPets::class, mappedBy: 'userId')]
    private Collection $lostPets;

    /**
     * @var Collection<int, FoundAnimals>
     */
    #[ORM\OneToMany(targetEntity: FoundAnimals::class, mappedBy: 'userId')]
    private Collection $foundAnimals;

    public function __construct()
    {
        $this->lostPets = new ArrayCollection();
        $this->foundAnimals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function isEmailNotifications(): ?bool
    {
        return $this->emailNotifications;
    }

    public function setEmailNotifications(bool $emailNotifications): static
    {
        $this->emailNotifications = $emailNotifications;

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

    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(\DateTime $lastLogin): static
    {
        $this->lastLogin = $lastLogin;

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

    public function isShelter(): ?bool
    {
        return $this->isShelter;
    }

    public function setIsShelter(bool $isShelter): static
    {
        $this->isShelter = $isShelter;

        return $this;
    }

    public function getShelterName(): ?string
    {
        return $this->shelterName;
    }

    public function setShelterName(?string $shelterName): static
    {
        $this->shelterName = $shelterName;

        return $this;
    }

    public function getShelterDescription(): ?string
    {
        return $this->shelterDescription;
    }

    public function setShelterDescription(?string $shelterDescription): static
    {
        $this->shelterDescription = $shelterDescription;

        return $this;
    }

    public function getShelterAddress(): ?string
    {
        return $this->shelterAddress;
    }

    public function setShelterAddress(?string $shelterAddress): static
    {
        $this->shelterAddress = $shelterAddress;

        return $this;
    }

    public function getShelterPhone(): ?string
    {
        return $this->shelterPhone;
    }

    public function setShelterPhone(?string $shelterPhone): static
    {
        $this->shelterPhone = $shelterPhone;

        return $this;
    }

    public function getShelterWebsite(): ?string
    {
        return $this->shelterWebsite;
    }

    public function setShelterWebsite(?string $shelterWebsite): static
    {
        $this->shelterWebsite = $shelterWebsite;

        return $this;
    }

    public function getShelterFacebook(): ?string
    {
        return $this->shelterFacebook;
    }

    public function setShelterFacebook(?string $shelterFacebook): static
    {
        $this->shelterFacebook = $shelterFacebook;

        return $this;
    }

    public function getShelterVerificationStatus(): ?string
    {
        return $this->shelterVerificationStatus;
    }

    public function setShelterVerificationStatus(string $shelterVerificationStatus): static
    {
        $this->shelterVerificationStatus = $shelterVerificationStatus;

        return $this;
    }

    public function getShelterVerificationDate(): ?\DateTime
    {
        return $this->shelterVerificationDate;
    }

    public function setShelterVerificationDate(\DateTime $shelterVerificationDate): static
    {
        $this->shelterVerificationDate = $shelterVerificationDate;

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): static
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    public function getResetTokenExpiresAt(): ?\DateTime
    {
        return $this->resetTokenExpiresAt;
    }

    public function setResetTokenExpiresAt(?\DateTime $resetTokenExpiresAt): static
    {
        $this->resetTokenExpiresAt = $resetTokenExpiresAt;

        return $this;
    }

    public function isResetTokenUsed(): ?bool
    {
        return $this->resetTokenUsed;
    }

    public function setResetTokenUsed(bool $resetTokenUsed): static
    {
        $this->resetTokenUsed = $resetTokenUsed;

        return $this;
    }

    /**
     * @return Collection<int, LostPets>
     */
    public function getLostPets(): Collection
    {
        return $this->lostPets;
    }

    public function addLostPet(LostPets $lostPet): static
    {
        if (!$this->lostPets->contains($lostPet)) {
            $this->lostPets->add($lostPet);
            $lostPet->setUserId($this);
        }

        return $this;
    }

    public function removeLostPet(LostPets $lostPet): static
    {
        if ($this->lostPets->removeElement($lostPet)) {
            // set the owning side to null (unless already changed)
            if ($lostPet->getUserId() === $this) {
                $lostPet->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FoundAnimals>
     */
    public function getFoundAnimals(): Collection
    {
        return $this->foundAnimals;
    }

    public function addFoundAnimal(FoundAnimals $foundAnimal): static
    {
        if (!$this->foundAnimals->contains($foundAnimal)) {
            $this->foundAnimals->add($foundAnimal);
            $foundAnimal->setUserId($this);
        }

        return $this;
    }

    public function removeFoundAnimal(FoundAnimals $foundAnimal): static
    {
        if ($this->foundAnimals->removeElement($foundAnimal)) {
            // set the owning side to null (unless already changed)
            if ($foundAnimal->getUserId() === $this) {
                $foundAnimal->setUserId(null);
            }
        }

        return $this;
    }
}
