<?php

namespace App\Controller;

use App\Entity\Animals;
use App\Entity\LostPets;
use App\Entity\FoundAnimals;
use App\Repository\UserRepository;
use App\Utils\ControllerUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/animal/{id}/{slug}', name: 'app_animal_show_slug', requirements: ['id' => '\d+', 'slug' => '.+'])]
    public function showBySlug(int $id, string $slug, EntityManagerInterface $entityManager): Response
    {
        // Buscar el animal por ID
        $animal = $entityManager->getRepository(Animals::class)->find($id);

        if (!$animal) {
            throw $this->createNotFoundException('Animal no encontrado');
        }

        // Verificar que el slug generado coincida con el proporcionado
        $expectedSlug = $animal->generateSlug();
        if ($slug !== $expectedSlug) {
            // Redirigir a la URL correcta
            return $this->redirectToRoute('app_animal_show_slug', ['id' => $id, 'slug' => $expectedSlug], 301);
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

    #[Route('/animal/delete/{id}', name: 'app_animal_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function deleteAnimal(Request $request, int $id, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        // Verificar que el usuario esté autenticado
        $user = ControllerUtils::requireAuthentication(
            $request,
            $userRepository,
            fn() => $this->getUser(),
            fn($type, $message) => $this->addFlash($type, $message),
            'Debes iniciar sesión para eliminar una publicación'
        );
        if (!$user) {
            return ControllerUtils::redirectToLogin(
                fn($type, $message) => $this->addFlash($type, $message),
                fn($route) => $this->redirectToRoute($route),
                'Debes iniciar sesión para eliminar una publicación'
            );
        }

        // Buscar el animal
        $animal = $entityManager->getRepository(Animals::class)->find($id);

        if (!$animal) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception('Animal no encontrado'),
                'Error'
            );
            return $this->redirectToRoute('app_home');
        }

        // Determinar si es un animal perdido o encontrado
        $lostPet = $entityManager->getRepository(LostPets::class)->findOneBy(['animalId' => $animal]);
        $foundAnimal = $entityManager->getRepository(FoundAnimals::class)->findOneBy(['animalId' => $animal]);

        $entityToDelete = null;

        if ($lostPet) {
            $entityToDelete = $lostPet;
        } elseif ($foundAnimal) {
            $entityToDelete = $foundAnimal;
        }

        // Verificar que el usuario sea el propietario de la publicación
        if (!ControllerUtils::checkOwnership(
            $user,
            $entityToDelete->getUserId(),
            fn($type, $message) => $this->addFlash($type, $message),
            'No tienes permisos para eliminar esta publicación'
        )) {
            return $this->redirectToRoute('app_home');
        }

        try {
            // Eliminar la entidad (esto también eliminará el animal asociado debido al cascade)
            $entityManager->remove($entityToDelete);
            $entityManager->flush();

            ControllerUtils::handleSuccess(
                fn($type, $message) => $this->addFlash($type, $message),
                'Animal eliminado correctamente'
            );
        } catch (\Exception $e) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                $e,
                'Error al eliminar el animal'
            );
        }

        // Redirigir a la página anterior
        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        // Si no hay referer, ir al home
        return $this->redirectToRoute('app_home');
    }
}
