<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Animals;
use App\Entity\FoundAnimals;
use App\Entity\Tags;
use App\Entity\AnimalTags;
use App\Entity\AnimalPhotos;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class MoreFoundAnimalsFixtures extends Fixture implements DependentFixtureInterface
{
   public function load(ObjectManager $manager): void
   {
      // Get existing users and tags
      $users = $manager->getRepository(User::class)->findAll();
      $tags = $manager->getRepository(Tags::class)->findAll();

      if (empty($users) || empty($tags)) {
         throw new \Exception('Users and Tags must be loaded first. Run AppFixtures first.');
      }

      // Create 50 more found animals for testing infinite scroll
      $this->createManyFoundAnimals($manager, $users, $tags);

      $manager->flush();
   }

   public function getDependencies(): array
   {
      return [
         AppFixtures::class,
      ];
   }

   private function createManyFoundAnimals(ObjectManager $manager, array $users, array $tags): void
   {
      $animalTypes = ['perro', 'gato', 'ave', 'conejo', 'hurón', 'tortuga'];
      $genders = ['male', 'female'];
      $sizes = ['small', 'medium', 'large', 'extra_large'];
      $colors = ['negro', 'blanco', 'marrón', 'gris', 'dorado', 'manchado', 'atigrado', 'tricolor'];
      $zones = ['centro', 'norte', 'sur', 'este', 'oeste'];

      $dogNames = ['Max', 'Luna', 'Buddy', 'Bella', 'Charlie', 'Lucy', 'Cooper', 'Daisy', 'Rocky', 'Molly', 'Jack', 'Sadie', 'Toby', 'Maggie', 'Duke', 'Sophie', 'Bear', 'Chloe', 'Zeus', 'Zoe'];
      $catNames = ['Whiskers', 'Mittens', 'Shadow', 'Princess', 'Tiger', 'Angel', 'Smokey', 'Patches', 'Oreo', 'Coco', 'Simba', 'Nala', 'Felix', 'Misty', 'Pepper', 'Snowball', 'Ginger', 'Boots', 'Midnight', 'Pumpkin'];
      $birdNames = ['Polly', 'Kiwi', 'Rio', 'Sunny', 'Blue', 'Charlie', 'Mango', 'Coco', 'Peanut', 'Ruby'];

      $descriptions = [
         'Encontrado vagando por el parque, parece estar bien cuidado',
         'Se acercó a nosotros pidiendo comida, muy amigable',
         'Encontrado cerca de la estación, llevaba collar pero sin identificación',
         'Visto varias veces en la zona, parece perdido',
         'Encontrado refugiándose de la lluvia, necesita un hogar',
         'Muy cariñoso, se ve que está acostumbrado a estar con personas',
         'Encontrado herido leve, ya está recuperado y listo para encontrar a su familia',
         'Aparentemente abandonado, busca una segunda oportunidad',
         'Encontrado asustado pero sano, necesita amor y cuidados',
         'Se perdió durante los fuegos artificiales de las fiestas',
      ];

      $addresses = [
         'Cerca del Parque del Retiro',
         'Avenida de la Castellana, altura del 150',
         'Plaza Mayor',
         'Cerca de la estación de Atocha',
         'Barrio de Malasaña',
         'Parque de El Capricho',
         'Cerca del Rastro',
         'Barrio de Chueca',
         'Paseo de la Chopera',
         'Cerca del Mercado de San Miguel',
         'Barrio de La Latina',
         'Parque del Oeste',
         'Cerca de Gran Vía',
         'Barrio de Salamanca',
         'Parque Juan Carlos I',
      ];

      for ($i = 1; $i <= 50; $i++) {
         // Create Animal
         $animal = new Animals();
         $animalType = $animalTypes[array_rand($animalTypes)];

         // Choose name based on type
         if ($animalType === 'perro') {
            $name = $dogNames[array_rand($dogNames)] . ' ' . $i;
         } elseif ($animalType === 'gato') {
            $name = $catNames[array_rand($catNames)] . ' ' . $i;
         } elseif ($animalType === 'ave') {
            $name = $birdNames[array_rand($birdNames)] . ' ' . $i;
         } else {
            $name = 'Mascota ' . $i;
         }

         $animal->setName($name);
         $animal->setAnimalType($animalType);
         $animal->setGender($genders[array_rand($genders)]);
         $animal->setSize($sizes[array_rand($sizes)]);
         $animal->setColor($colors[array_rand($colors)]);
         $animal->setAge(rand(1, 15) . ' años');
         $animal->setDescription($descriptions[array_rand($descriptions)]);
         $animal->setStatus('FOUND');

         // Random creation date within last 30 days
         $daysAgo = rand(0, 30);
         $createdAt = new \DateTimeImmutable("-{$daysAgo} days");
         $animal->setCreatedAt($createdAt);
         $animal->setUpdatedAt($createdAt);

         $manager->persist($animal);

         // Add random tags (1-4 tags per animal)
         $numTags = rand(1, 4);
         $shuffledTags = $tags;
         shuffle($shuffledTags);
         $selectedTags = array_slice($shuffledTags, 0, $numTags);

         foreach ($selectedTags as $tag) {
            $animalTag = new AnimalTags();
            $animalTag->setAnimalId($animal);
            $animalTag->setTagId($tag);
            $animalTag->setCreatedAt($createdAt);
            $manager->persist($animalTag);
         }

         // Create FoundAnimals entry
         $foundAnimal = new FoundAnimals();
         $foundAnimal->setAnimalId($animal);
         $foundAnimal->setUserId($users[array_rand($users)]);

         // Random found date within the creation date
         $foundDaysAgo = rand($daysAgo, $daysAgo + 5);
         $foundDate = new \DateTime("-{$foundDaysAgo} days");
         $foundAnimal->setFoundDate($foundDate);

         // Random time
         $hour = str_pad(rand(6, 22), 2, '0', STR_PAD_LEFT);
         $minute = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
         $foundAnimal->setFoundTime(new \DateTime("{$hour}:{$minute}"));

         $foundAnimal->setFoundZone($zones[array_rand($zones)]);
         $foundAnimal->setFoundAddress($addresses[array_rand($addresses)]);

         $circumstances = [
            'Vagando solo por la calle',
            'Refugiándose del mal tiempo',
            'Buscando comida en los contenedores',
            'Siguiendo a personas por la calle',
            'Asustado en un parque',
            'Cerca de una parada de autobús',
            'En un portal de un edificio',
            'Junto a coches aparcados',
         ];

         $foundAnimal->setFoundCircumstances($circumstances[array_rand($circumstances)]);

         $notes = [
            'Parece estar bien cuidado, probablemente tiene dueño',
            'Necesita revisión veterinaria',
            'Muy sociable con las personas',
            'Un poco tímido al principio',
            'Se ve que está acostumbrado a estar en casa',
            'Necesita cuidados especiales',
            'Muy activo y juguetón',
            'Tranquilo y obediente',
         ];

         $foundAnimal->setAdditionalNotes($notes[array_rand($notes)]);
         $foundAnimal->setCreatedAt(new \DateTimeImmutable("-{$daysAgo} days"));
         $foundAnimal->setUpdatedAt(new \DateTimeImmutable("-{$daysAgo} days"));

         $manager->persist($foundAnimal);

         // Occasionally add a photo (30% chance)
         if (rand(1, 100) <= 30) {
            $animalPhoto = new AnimalPhotos();
            $animalPhoto->setAnimalId($animal);
            $animalPhoto->setFilePath('placeholder_images/animal_' . ($i % 10 + 1) . '.jpg');
            $animalPhoto->setFilename('found_animal_' . $i . '.jpg');
            $animalPhoto->setOriginalFilename('found_animal_' . $i . '.jpg');
            $animalPhoto->setFileSize(rand(100000, 500000));
            $animalPhoto->setMimeType('image/jpeg');
            $animalPhoto->setIsPrimary(true);
            $animalPhoto->setCreatedAt($createdAt);

            $manager->persist($animalPhoto);
         }

         // Flush every 10 animals to avoid memory issues
         if ($i % 10 === 0) {
            $manager->flush();
            $manager->clear();

            // Re-fetch users and tags after clearing
            $users = $manager->getRepository(User::class)->findAll();
            $tags = $manager->getRepository(Tags::class)->findAll();
         }
      }
   }
}
