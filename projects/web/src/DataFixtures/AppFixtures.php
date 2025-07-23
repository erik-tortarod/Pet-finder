<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Animals;
use App\Entity\LostPets;
use App\Entity\FoundAnimals;
use App\Entity\Tags;
use App\Entity\AnimalTags;
use App\Entity\AnimalPhotos;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
   private UserPasswordHasherInterface $passwordHasher;

   public function __construct(UserPasswordHasherInterface $passwordHasher)
   {
      $this->passwordHasher = $passwordHasher;
   }

   public function load(ObjectManager $manager): void
   {
      // Crear usuarios
      $users = $this->createUsers($manager);

      // Crear tags
      $tags = $this->createTags($manager);

      // Crear animales perdidos
      $this->createLostPets($manager, $users, $tags);

      // Crear animales encontrados
      $this->createFoundAnimals($manager, $users, $tags);

      $manager->flush();
   }

   private function createUsers(ObjectManager $manager): array
   {
      $users = [];

      // Usuario normal 1
      $user1 = new User();
      $user1->setEmail('juan.perez@email.com');
      $user1->setPassword($this->passwordHasher->hashPassword($user1, 'password123'));
      $user1->setFirstName('Juan');
      $user1->setLastName('Pérez');
      $user1->setPhone('+34 600 123 456');
      $user1->setEmailNotifications(true);
      $user1->setCreatedAt(new \DateTimeImmutable());
      $user1->setUpdatedAt(new \DateTimeImmutable());
      $user1->setLastLogin(new \DateTime());
      $user1->setIsActive(true);
      $user1->setIsShelter(false);
      $user1->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

      $manager->persist($user1);
      $users[] = $user1;

      // Usuario normal 2
      $user2 = new User();
      $user2->setEmail('maria.garcia@email.com');
      $user2->setPassword($this->passwordHasher->hashPassword($user2, 'password123'));
      $user2->setFirstName('María');
      $user2->setLastName('García');
      $user2->setPhone('+34 600 789 012');
      $user2->setEmailNotifications(false);
      $user2->setCreatedAt(new \DateTimeImmutable());
      $user2->setUpdatedAt(new \DateTimeImmutable());
      $user2->setLastLogin(new \DateTime());
      $user2->setIsActive(true);
      $user2->setIsShelter(false);
      $user2->setRoles(['ROLE_USER']);

      $manager->persist($user2);
      $users[] = $user2;

      // Refugio
      $shelter = new User();
      $shelter->setEmail('refugio.amigos@email.com');
      $shelter->setPassword($this->passwordHasher->hashPassword($shelter, 'password123'));
      $shelter->setFirstName('Refugio');
      $shelter->setLastName('Amigos');
      $shelter->setPhone('+34 900 123 456');
      $shelter->setEmailNotifications(true);
      $shelter->setCreatedAt(new \DateTimeImmutable());
      $shelter->setUpdatedAt(new \DateTimeImmutable());
      $shelter->setLastLogin(new \DateTime());
      $shelter->setIsActive(true);
      $shelter->setIsShelter(true);
      $shelter->setShelterName('Refugio Amigos de los Animales');
      $shelter->setShelterDescription('Refugio dedicado al cuidado y adopción de animales abandonados');
      $shelter->setShelterAddress('Calle de la Esperanza, 123, Madrid');
      $shelter->setShelterPhone('+34 900 123 456');
      $shelter->setShelterWebsite('https://refugioamigos.es');
      $shelter->setShelterFacebook('https://facebook.com/refugioamigos');
      $shelter->setShelterVerificationStatus('VERIFIED');
      $shelter->setShelterVerificationDate(new \DateTime());
      $shelter->setRoles(['ROLE_USER', 'ROLE_SHELTER']);

      $manager->persist($shelter);
      $users[] = $shelter;

      return $users;
   }

   private function createTags(ObjectManager $manager): array
   {
      $tagNames = [
         'Amigable',
         'Juguetón',
         'Tranquilo',
         'Energético',
         'Cariñoso',
         'Independiente',
         'Sociable',
         'Miedoso',
         'Valiente',
         'Inteligente',
         'Obediente',
         'Territorial',
         'Protector',
         'Curioso',
         'Paciente',
         'Nervioso',
         'Relajado',
         'Activo',
         'Dormilón',
         'Hambriento'
      ];

      $tags = [];
      foreach ($tagNames as $tagName) {
         $tag = new Tags();
         $tag->setName($tagName);
         $tag->setIsActive(true);
         $tag->setCreatedAt(new \DateTimeImmutable());

         $manager->persist($tag);
         $tags[] = $tag;
      }

      return $tags;
   }

   private function createLostPets(ObjectManager $manager, array $users, array $tags): void
   {
      $lostPetsData = [
         [
            'name' => 'Luna',
            'animalType' => 'Perro',
            'gender' => 'female',
            'size' => 'medium',
            'color' => 'Blanco y negro',
            'age' => '3 años',
            'description' => 'Luna es una perrita muy cariñosa que se perdió mientras paseaba. Tiene una mancha blanca en el pecho.',
            'lostZone' => 'Centro de Madrid',
            'lostAddress' => 'Calle Gran Vía, 28',
            'lostCircumstances' => 'Se soltó de la correa durante un paseo',
            'rewardAmount' => '200€',
            'rewardDescription' => 'Recompensa por información que lleve a su recuperación',
            'tags' => ['Amigable', 'Cariñoso', 'Sociable'],
            'user' => $users[0],
            'photos' => [
               [
                  'url' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?w=400&h=300&fit=crop',
                  'filename' => 'luna_primary.jpg',
                  'isPrimary' => true
               ],
               [
                  'url' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400&h=300&fit=crop',
                  'filename' => 'luna_secondary.jpg',
                  'isPrimary' => false
               ]
            ]
         ],
         [
            'name' => 'Max',
            'animalType' => 'Gato',
            'gender' => 'male',
            'size' => 'small',
            'color' => 'Naranja',
            'age' => '2 años',
            'description' => 'Max es un gato naranja muy juguetón. Tiene una cola muy larga y es muy cariñoso.',
            'lostZone' => 'Barrio de Salamanca',
            'lostAddress' => 'Calle de Velázquez, 45',
            'lostCircumstances' => 'Se escapó por la ventana',
            'rewardAmount' => '150€',
            'rewardDescription' => 'Recompensa por su devolución',
            'tags' => ['Juguetón', 'Cariñoso', 'Curioso'],
            'user' => $users[1],
            'photos' => [
               [
                  'url' => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400&h=300&fit=crop',
                  'filename' => 'max_primary.jpg',
                  'isPrimary' => true
               ],
               [
                  'url' => 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400&h=300&fit=crop',
                  'filename' => 'max_secondary.jpg',
                  'isPrimary' => false
               ]
            ]
         ],
         [
            'name' => 'Rocky',
            'animalType' => 'Perro',
            'gender' => 'male',
            'size' => 'large',
            'color' => 'Marrón',
            'age' => '5 años',
            'description' => 'Rocky es un perro grande y protector. Es muy leal a su familia.',
            'lostZone' => 'Vallecas',
            'lostAddress' => 'Avenida de la Albufera, 12',
            'lostCircumstances' => 'Se perdió durante una tormenta',
            'rewardAmount' => '300€',
            'rewardDescription' => 'Recompensa por información',
            'tags' => ['Protector', 'Leal', 'Valiente'],
            'user' => $users[0],
            'photos' => [
               [
                  'url' => 'https://images.unsplash.com/photo-1558788353-f76d92427f16?w=400&h=300&fit=crop',
                  'filename' => 'rocky_primary.jpg',
                  'isPrimary' => true
               ],
               [
                  'url' => 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?w=400&h=300&fit=crop',
                  'filename' => 'rocky_secondary.jpg',
                  'isPrimary' => false
               ]
            ]
         ]
      ];

      foreach ($lostPetsData as $data) {
         // Crear animal
         $animal = new Animals();
         $animal->setName($data['name']);
         $animal->setAnimalType($data['animalType']);
         $animal->setGender($data['gender']);
         $animal->setSize($data['size']);
         $animal->setColor($data['color']);
         $animal->setAge($data['age']);
         $animal->setDescription($data['description']);
         $animal->setStatus('LOST');
         $animal->setCreatedAt(new \DateTimeImmutable());
         $animal->setUpdatedAt(new \DateTimeImmutable());

         $manager->persist($animal);

         // Crear mascota perdida
         $lostPet = new LostPets();
         $lostPet->setAnimalId($animal);
         $lostPet->setUserId($data['user']);
         $lostPet->setLostDate(new \DateTime('-2 days'));
         $lostPet->setLostTime(new \DateTime('14:30:00'));
         $lostPet->setLostZone($data['lostZone']);
         $lostPet->setLostAddress($data['lostAddress']);
         $lostPet->setLostCircumstances($data['lostCircumstances']);
         $lostPet->setRewardAmount($data['rewardAmount']);
         $lostPet->setRewardDescription($data['rewardDescription']);
         $lostPet->setCreatedAt(new \DateTimeImmutable());
         $lostPet->setUpdatedAt(new \DateTimeImmutable());

         $manager->persist($lostPet);

         // Agregar fotos
         foreach ($data['photos'] as $photoData) {
            $photo = new AnimalPhotos();
            $photo->setAnimalId($animal);
            $photo->setFilename($photoData['filename']);
            $photo->setOriginalFilename($photoData['filename']);
            $photo->setFilePath($photoData['url']); // Usar URL como filePath
            $photo->setFileSize(rand(500, 2000)); // Tamaño aleatorio entre 500KB y 2MB
            $photo->setMimeType('image/jpeg');
            $photo->setCreatedAt(new \DateTimeImmutable());
            $photo->setIsPrimary($photoData['isPrimary']);

            $manager->persist($photo);
         }

         // Agregar tags
         foreach ($data['tags'] as $tagName) {
            $tag = $this->findTagByName($tags, $tagName);
            if ($tag) {
               $animalTag = new AnimalTags();
               $animalTag->setAnimalId($animal);
               $animalTag->setTagId($tag);
               $animalTag->setCreatedAt(new \DateTimeImmutable());

               $manager->persist($animalTag);
            }
         }
      }
   }

   private function createFoundAnimals(ObjectManager $manager, array $users, array $tags): void
   {
      $foundAnimalsData = [
         [
            'name' => 'Pelusa',
            'animalType' => 'Gato',
            'gender' => 'female',
            'size' => 'small',
            'color' => 'Gris',
            'age' => '1 año',
            'description' => 'Gatita gris muy tranquila y cariñosa. Se encontró en el parque.',
            'foundZone' => 'Parque del Retiro',
            'foundAddress' => 'Paseo de la Argentina, 1',
            'foundCircumstances' => 'Se encontró sola en el parque, parece perdida',
            'additionalNotes' => 'Tiene un collar rojo pero sin identificación',
            'tags' => ['Tranquilo', 'Cariñoso', 'Miedoso'],
            'user' => $users[1],
            'photos' => [
               [
                  'url' => 'https://images.unsplash.com/photo-1513360371669-4adf3dd7dff8?w=400&h=300&fit=crop',
                  'filename' => 'pelusa_primary.jpg',
                  'isPrimary' => true
               ],
               [
                  'url' => 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400&h=300&fit=crop',
                  'filename' => 'pelusa_secondary.jpg',
                  'isPrimary' => false
               ]
            ]
         ],
         [
            'name' => null,
            'animalType' => 'Perro',
            'gender' => 'male',
            'size' => 'large',
            'color' => 'Negro',
            'age' => '4 años',
            'description' => 'Perro grande y fuerte, muy amigable con las personas.',
            'foundZone' => 'Chamberí',
            'foundAddress' => 'Calle de Bravo Murillo, 78',
            'foundCircumstances' => 'Se encontró vagando por la calle',
            'additionalNotes' => 'Parece estar bien cuidado, posiblemente se perdió recientemente',
            'tags' => ['Amigable', 'Energético', 'Sociable'],
            'user' => $users[2],
            'photos' => [
               [
                  'url' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400&h=300&fit=crop',
                  'filename' => 'thor_primary.jpg',
                  'isPrimary' => true
               ],
               [
                  'url' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?w=400&h=300&fit=crop',
                  'filename' => 'thor_secondary.jpg',
                  'isPrimary' => false
               ]
            ]
         ],
         [
            'name' => 'Mittens',
            'animalType' => 'Gato',
            'gender' => 'male',
            'size' => 'medium',
            'color' => 'Blanco y negro',
            'age' => '3 años',
            'description' => 'Gato blanco y negro con patas blancas como guantes.',
            'foundZone' => 'Malasaña',
            'foundAddress' => 'Calle de la Palma, 23',
            'foundCircumstances' => 'Se encontró en el patio de un edificio',
            'additionalNotes' => 'Es muy independiente pero se deja acariciar',
            'tags' => ['Independiente', 'Tranquilo', 'Curioso'],
            'user' => $users[0],
            'photos' => [
               [
                  'url' => 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400&h=300&fit=crop',
                  'filename' => 'mittens_primary.jpg',
                  'isPrimary' => true
               ],
               [
                  'url' => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400&h=300&fit=crop',
                  'filename' => 'mittens_secondary.jpg',
                  'isPrimary' => false
               ]
            ]
         ]
      ];

      foreach ($foundAnimalsData as $data) {
         // Crear animal
         $animal = new Animals();
         $animal->setName($data['name']);
         $animal->setAnimalType($data['animalType']);
         $animal->setGender($data['gender']);
         $animal->setSize($data['size']);
         $animal->setColor($data['color']);
         $animal->setAge($data['age']);
         $animal->setDescription($data['description']);
         $animal->setStatus('FOUND');
         $animal->setCreatedAt(new \DateTimeImmutable());
         $animal->setUpdatedAt(new \DateTimeImmutable());

         $manager->persist($animal);

         // Crear animal encontrado
         $foundAnimal = new FoundAnimals();
         $foundAnimal->setAnimalId($animal);
         $foundAnimal->setUserId($data['user']);
         $foundAnimal->setFoundDate(new \DateTime('-1 day'));
         $foundAnimal->setFoundTime(new \DateTime('10:15:00'));
         $foundAnimal->setFoundZone($data['foundZone']);
         $foundAnimal->setFoundAddress($data['foundAddress']);
         $foundAnimal->setFoundCircumstances($data['foundCircumstances']);
         $foundAnimal->setAdditionalNotes($data['additionalNotes']);
         $foundAnimal->setCreatedAt(new \DateTimeImmutable());
         $foundAnimal->setUpdatedAt(new \DateTimeImmutable());

         $manager->persist($foundAnimal);

         // Agregar fotos
         foreach ($data['photos'] as $photoData) {
            $photo = new AnimalPhotos();
            $photo->setAnimalId($animal);
            $photo->setFilename($photoData['filename']);
            $photo->setOriginalFilename($photoData['filename']);
            $photo->setFilePath($photoData['url']); // Usar URL como filePath
            $photo->setFileSize(rand(500, 2000)); // Tamaño aleatorio entre 500KB y 2MB
            $photo->setMimeType('image/jpeg');
            $photo->setCreatedAt(new \DateTimeImmutable());
            $photo->setIsPrimary($photoData['isPrimary']);

            $manager->persist($photo);
         }

         // Agregar tags
         foreach ($data['tags'] as $tagName) {
            $tag = $this->findTagByName($tags, $tagName);
            if ($tag) {
               $animalTag = new AnimalTags();
               $animalTag->setAnimalId($animal);
               $animalTag->setTagId($tag);
               $animalTag->setCreatedAt(new \DateTimeImmutable());

               $manager->persist($animalTag);
            }
         }
      }
   }

   private function findTagByName(array $tags, string $name): ?Tags
   {
      foreach ($tags as $tag) {
         if ($tag->getName() === $name) {
            return $tag;
         }
      }
      return null;
   }
}
