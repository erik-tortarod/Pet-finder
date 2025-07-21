<?php

namespace App\Controller;

use App\Entity\Animals;
use App\Entity\LostPets;
use App\Entity\FoundAnimals;
use App\Repository\UserRepository;
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

    #[Route('/animal/lost/{id}/delete', name: 'app_animal_lost_delete', methods: ['POST'])]
    public function deleteLostPet(Request $request, int $id, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        // Verificar que el usuario esté autenticado
        $user = $this->getUserFromSession($request, $userRepository);
        if (!$user) {
            $this->addFlash('error', 'Debes iniciar sesión para eliminar una publicación');
            return $this->redirectToRoute('app_auth_login');
        }

        // Buscar la mascota perdida
        $lostPet = $entityManager->getRepository(LostPets::class)->find($id);

        if (!$lostPet) {
            $this->addFlash('error', 'Mascota perdida no encontrada');
            return $this->redirectToRoute('app_user_lost_pets');
        }

        // Verificar que el usuario sea el propietario de la publicación
        if ($lostPet->getUserId() !== $user) {
            $this->addFlash('error', 'No tienes permisos para eliminar esta publicación');
            return $this->redirectToRoute('app_user_lost_pets');
        }

        try {
            // Eliminar la mascota perdida (esto también eliminará el animal asociado debido al cascade)
            $entityManager->remove($lostPet);
            $entityManager->flush();

            $this->addFlash('success', 'Mascota perdida eliminada correctamente');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al eliminar la mascota perdida');
        }

        return $this->redirectToRoute('app_user_lost_pets');
    }

    #[Route('/animal/found/{id}/delete', name: 'app_animal_found_delete', methods: ['POST'])]
    public function deleteFoundPet(Request $request, int $id, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        // Verificar que el usuario esté autenticado
        $user = $this->getUserFromSession($request, $userRepository);
        if (!$user) {
            $this->addFlash('error', 'Debes iniciar sesión para eliminar una publicación');
            return $this->redirectToRoute('app_auth_login');
        }

        // Buscar el animal encontrado
        $foundAnimal = $entityManager->getRepository(FoundAnimals::class)->find($id);

        if (!$foundAnimal) {
            $this->addFlash('error', 'Animal encontrado no encontrado');
            return $this->redirectToRoute('app_user_found_pets');
        }

        // Verificar que el usuario sea el propietario de la publicación
        if ($foundAnimal->getUserId() !== $user) {
            $this->addFlash('error', 'No tienes permisos para eliminar esta publicación');
            return $this->redirectToRoute('app_user_found_pets');
        }

        try {
            // Eliminar el animal encontrado (esto también eliminará el animal asociado debido al cascade)
            $entityManager->remove($foundAnimal);
            $entityManager->flush();

            $this->addFlash('success', 'Animal encontrado eliminado correctamente');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al eliminar el animal encontrado');
        }

        return $this->redirectToRoute('app_user_found_pets');
    }

    /**
     * Obtiene el usuario desde la sesión manual
     */
    private function getUserFromSession(Request $request, UserRepository $userRepository)
    {
        // Primero intentar con el sistema de seguridad de Symfony
        $user = $this->getUser();
        if ($user) {
            return $user;
        }

        // Si no funciona, usar la sesión manual
        $session = $request->getSession();
        $userId = $session->get('user_id');

        if (!$userId) {
            return null;
        }

        return $userRepository->find($userId);
    }
}
