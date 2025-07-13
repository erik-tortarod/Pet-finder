<?php

namespace App\Controller;

use App\Entity\Animals;
use App\Entity\LostPets;
use App\Entity\FoundAnimals;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AnimalController extends AbstractController
{
    #[Route('/animal', name: 'app_animal')]
    public function index(): Response
    {
        return $this->render('animal/index.html.twig', [
            'controller_name' => 'AnimalController',
        ]);
    }

    #[Route('/animal/{id}', name: 'app_animal_show', requirements: ['id' => '\d+'])]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        // Buscar el animal con todas sus relaciones
        $animal = $entityManager->getRepository(Animals::class)->find($id);

        if (!$animal) {
            throw $this->createNotFoundException('Animal no encontrado');
        }

        // Buscar si es un animal perdido
        $lostPet = $entityManager->getRepository(LostPets::class)->findOneBy(['animalId' => $animal]);

        // Buscar si es un animal encontrado
        $foundAnimal = $entityManager->getRepository(FoundAnimals::class)->findOneBy(['animalId' => $animal]);

        // Obtener la foto principal
        $primaryPhoto = null;
        foreach ($animal->getAnimalPhotos() as $photo) {
            if ($photo->isPrimary()) {
                $primaryPhoto = $photo;
                break;
            }
        }

        // Si no hay foto principal, tomar la primera disponible
        if (!$primaryPhoto && $animal->getAnimalPhotos()->count() > 0) {
            $primaryPhoto = $animal->getAnimalPhotos()->first();
        }

        return $this->render('animal/show.html.twig', [
            'animal' => $animal,
            'lostPet' => $lostPet,
            'foundAnimal' => $foundAnimal,
            'primaryPhoto' => $primaryPhoto,
        ]);
    }

    #[Route('/animal/{slug}', name: 'app_animal_show_slug', requirements: ['slug' => '.+'])]
    public function showBySlug(string $slug, EntityManagerInterface $entityManager): Response
    {
        // Extraer el ID del final del slug
        $parts = explode('-', $slug);
        $id = end($parts);

        if (!is_numeric($id)) {
            throw $this->createNotFoundException('URL inválida');
        }

        // Buscar el animal por ID
        $animal = $entityManager->getRepository(Animals::class)->find((int)$id);

        if (!$animal) {
            throw $this->createNotFoundException('Animal no encontrado');
        }

        // Verificar que el slug generado coincida con el proporcionado
        $expectedSlug = $animal->generateSlug();
        if ($slug !== $expectedSlug) {
            // Redirigir a la URL correcta
            return $this->redirectToRoute('app_animal_show_slug', ['slug' => $expectedSlug], 301);
        }

        // Buscar si es un animal perdido
        $lostPet = $entityManager->getRepository(LostPets::class)->findOneBy(['animalId' => $animal]);

        // Buscar si es un animal encontrado
        $foundAnimal = $entityManager->getRepository(FoundAnimals::class)->findOneBy(['animalId' => $animal]);

        // Obtener la foto principal
        $primaryPhoto = null;
        foreach ($animal->getAnimalPhotos() as $photo) {
            if ($photo->isPrimary()) {
                $primaryPhoto = $photo;
                break;
            }
        }

        // Si no hay foto principal, tomar la primera disponible
        if (!$primaryPhoto && $animal->getAnimalPhotos()->count() > 0) {
            $primaryPhoto = $animal->getAnimalPhotos()->first();
        }

        return $this->render('animal/show.html.twig', [
            'animal' => $animal,
            'lostPet' => $lostPet,
            'foundAnimal' => $foundAnimal,
            'primaryPhoto' => $primaryPhoto,
        ]);
    }
}
