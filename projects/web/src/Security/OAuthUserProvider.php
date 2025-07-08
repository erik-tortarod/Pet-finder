<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthUserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{
   public function __construct(
      private UserRepository $userRepository,
      private UserPasswordHasherInterface $passwordHasher
   ) {}

   public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
   {
      $googleUser = $response->getData();
      $email = $googleUser['email'] ?? null;

      if (!$email) {
         throw new UnsupportedUserException('No se pudo obtener el email de Google');
      }

      // Buscar si el usuario ya existe
      $user = $this->userRepository->findOneBy(['email' => $email]);

      if (!$user) {
         // Crear nuevo usuario con datos de Google
         $user = new User();
         $user->setEmail($email);
         $user->setFirstName($googleUser['given_name'] ?? '');
         $user->setLastName($googleUser['family_name'] ?? '');

         // Generar contraseña aleatoria para usuarios de Google
         $randomPassword = bin2hex(random_bytes(32));
         $hashedPassword = $this->passwordHasher->hashPassword($user, $randomPassword);
         $user->setPassword($hashedPassword);

         $user->setCreatedAt(new \DateTimeImmutable());
         $user->setUpdatedAt(new \DateTimeImmutable());
         $user->setRoles(['ROLE_USER']);
         $user->setIsActive(true);
         $user->setLastLogin(new \DateTime());
         $user->setEmailNotifications(false);
         $user->setIsShelter(false);

         $this->userRepository->add($user, true);
      } else {
         // Actualizar último login
         $user->setLastLogin(new \DateTime());
         $this->userRepository->add($user, true);
      }

      return $user;
   }

   public function loadUserByIdentifier(string $identifier): UserInterface
   {
      $user = $this->userRepository->findOneBy(['email' => $identifier]);

      if (!$user) {
         throw new UserNotFoundException(sprintf('Usuario con email "%s" no encontrado.', $identifier));
      }

      return $user;
   }

   public function refreshUser(UserInterface $user): UserInterface
   {
      if (!$user instanceof User) {
         throw new UnsupportedUserException(sprintf('Instancias de "%s" no están soportadas.', $user::class));
      }

      return $this->loadUserByIdentifier($user->getUserIdentifier());
   }

   public function supportsClass(string $class): bool
   {
      return User::class === $class || is_subclass_of($class, User::class);
   }
}
