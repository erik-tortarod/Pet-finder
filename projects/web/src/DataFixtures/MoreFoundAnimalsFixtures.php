<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Animals;
use App\Entity\FoundAnimals;
use App\Entity\LostPets;
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

        // Create 20 more found animals for testing infinite scroll
        $this->createFoundAnimals($manager, $users, $tags);

        // Create 15 lost animals for testing infinite scroll
        $this->createLostAnimals($manager, $users, $tags);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AppFixtures::class,
        ];
    }

    private function createFoundAnimals(ObjectManager $manager, array $users, array $tags): void
    {
        $animalTypes = ['perro', 'gato', 'ave', 'conejo'];
        $genders = ['male', 'female'];
        $sizes = ['small', 'medium', 'large', 'extra_large'];
        $colors = ['negro', 'blanco', 'marrón', 'gris', 'dorado'];
        $zones = ['centro', 'norte', 'sur', 'este', 'oeste'];

        $names = ['Max', 'Luna', 'Buddy', 'Bella', 'Charlie', 'Lucy', 'Cooper', 'Daisy', 'Rocky', 'Molly'];

        $descriptions = [
            'Encontrado vagando por el parque',
            'Se acercó pidiendo comida, muy amigable',
            'Encontrado cerca de la estación',
            'Parece perdido, necesita un hogar',
            'Muy cariñoso con las personas',
        ];

        $addresses = [
            'Cerca del Parque del Retiro',
            'Avenida de la Castellana',
            'Plaza Mayor',
            'Cerca de la estación de Atocha',
            'Barrio de Malasaña',
        ];

        for ($i = 1; $i <= 20; $i++) {
            // Create Animal
            $animal = new Animals();
            $animal->setName($names[array_rand($names)] . ' F' . $i);
            $animal->setAnimalType($animalTypes[array_rand($animalTypes)]);
            $animal->setGender($genders[array_rand($genders)]);
            $animal->setSize($sizes[array_rand($sizes)]);
            $animal->setColor($colors[array_rand($colors)]);
            $animal->setAge(rand(1, 10) . ' años');
            $animal->setDescription($descriptions[array_rand($descriptions)]);
            $animal->setStatus('FOUND');

            $daysAgo = rand(0, 30);
            $createdAt = new \DateTimeImmutable("-{$daysAgo} days");
            $animal->setCreatedAt($createdAt);
            $animal->setUpdatedAt($createdAt);

            $manager->persist($animal);

            // Add one random tag
            $randomTag = $tags[array_rand($tags)];
            $animalTag = new AnimalTags();
            $animalTag->setAnimalId($animal);
            $animalTag->setTagId($randomTag);
            $animalTag->setCreatedAt($createdAt);
            $manager->persist($animalTag);

            // Create FoundAnimals entry
            $foundAnimal = new FoundAnimals();
            $foundAnimal->setAnimalId($animal);
            $foundAnimal->setUserId($users[array_rand($users)]);

            $foundDate = new \DateTime("-{$daysAgo} days");
            $foundAnimal->setFoundDate($foundDate);
            $foundAnimal->setFoundTime(new \DateTime("10:00"));
            $foundAnimal->setFoundZone($zones[array_rand($zones)]);
            $foundAnimal->setFoundAddress($addresses[array_rand($addresses)]);
            $foundAnimal->setFoundCircumstances('Vagando solo por la calle');
            $foundAnimal->setCreatedAt($createdAt);
            $foundAnimal->setUpdatedAt($createdAt);

            $manager->persist($foundAnimal);
        }
    }

    private function createLostAnimals(ObjectManager $manager, array $users, array $tags): void
    {
        $animalTypes = ['perro', 'gato', 'ave', 'conejo'];
        $genders = ['male', 'female'];
        $sizes = ['small', 'medium', 'large', 'extra_large'];
        $colors = ['negro', 'blanco', 'marrón', 'gris', 'dorado'];
        $zones = ['centro', 'norte', 'sur', 'este', 'oeste'];

        $names = ['Rex', 'Lola', 'Bruno', 'Nina', 'Thor', 'Mia', 'Leo', 'Emma'];

        $descriptions = [
            'Se escapó del jardín',
            'Perdido después de los fuegos artificiales',
            'No ha regresado a casa',
            'Se asustó y huyó en el parque',
            'Muy cariñoso, responde a su nombre',
        ];

        $addresses = [
            'Última vez visto en el Parque del Retiro',
            'Desapareció en la Avenida de la Castellana',
            'Perdido cerca de Plaza Mayor',
            'Última vez visto en Atocha',
            'Desapareció en Malasaña',
        ];

        for ($i = 1; $i <= 15; $i++) {
            // Create Animal
            $animal = new Animals();
            $animal->setName($names[array_rand($names)] . ' L' . $i);
            $animal->setAnimalType($animalTypes[array_rand($animalTypes)]);
            $animal->setGender($genders[array_rand($genders)]);
            $animal->setSize($sizes[array_rand($sizes)]);
            $animal->setColor($colors[array_rand($colors)]);
            $animal->setAge(rand(1, 10) . ' años');
            $animal->setDescription($descriptions[array_rand($descriptions)]);
            $animal->setStatus('LOST');

            $daysAgo = rand(0, 20);
            $createdAt = new \DateTimeImmutable("-{$daysAgo} days");
            $animal->setCreatedAt($createdAt);
            $animal->setUpdatedAt($createdAt);

            $manager->persist($animal);

            // Add one random tag
            $randomTag = $tags[array_rand($tags)];
            $animalTag = new AnimalTags();
            $animalTag->setAnimalId($animal);
            $animalTag->setTagId($randomTag);
            $animalTag->setCreatedAt($createdAt);
            $manager->persist($animalTag);

            // Create LostPets entry
            $lostPet = new LostPets();
            $lostPet->setAnimalId($animal);
            $lostPet->setUserId($users[array_rand($users)]);

            $lostDate = new \DateTime("-{$daysAgo} days");
            $lostPet->setLostDate($lostDate);
            $lostPet->setLostTime(new \DateTime("15:00"));
            $lostPet->setLostZone($zones[array_rand($zones)]);
            $lostPet->setLostAddress($addresses[array_rand($addresses)]);
            $lostPet->setLostCircumstances('Se escapó del jardín');
            $lostPet->setRewardDescription('Recompensa disponible');
            $lostPet->setCreatedAt($createdAt);
            $lostPet->setUpdatedAt($createdAt);

            $manager->persist($lostPet);
        }
    }
}
