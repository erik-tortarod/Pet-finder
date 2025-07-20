<?php

namespace App\Controller;

use App\Entity\Animals;
use App\Entity\FoundAnimals;
use App\Entity\Tags;
use App\Entity\AnimalTags;
use App\Entity\AnimalPhotos;
use App\Form\FoundPetType;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\UserRepository;

final class FoundPetsController extends AbstractController
{
    #[Route('/found/pets', name: 'app_found_pets')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $foundAnimalsRepository = $entityManager->getRepository(FoundAnimals::class);
        $foundAnimals = $foundAnimalsRepository->createQueryBuilder('fa')
            ->leftJoin('fa.animalId', 'a')
            ->leftJoin('a.animalPhotos', 'ap')
            ->leftJoin('fa.userId', 'u')
            ->addSelect('a', 'ap', 'u')
            ->orderBy('fa.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('found_pets/index.html.twig', [
            'foundAnimals' => $foundAnimals,
        ]);
    }

    #[Route('/found/pets/create', name: 'app_found_pets_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, FileUploadService $fileUploadService, UserRepository $userRepository): Response
    {
        // Verificar que el usuario esté autenticado usando sesión manual
        $user = $this->getUserFromSession($request, $userRepository);
        if (!$user) {
            $this->addFlash('error', 'Debes iniciar sesión para crear una publicación');
            return $this->redirectToRoute('app_auth_login');
        }

        $form = $this->createForm(FoundPetType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Crear el animal
                $animal = new Animals();
                $animal->setName($form->get('animalName')->getData() ?: 'Sin nombre');
                $animal->setAnimalType($form->get('animalType')->getData());
                $animal->setGender($form->get('animalGender')->getData());
                $animal->setSize($form->get('animalSize')->getData());
                $animal->setColor($form->get('animalColor')->getData());
                $animal->setAge($form->get('animalAge')->getData());
                $animal->setDescription($form->get('animalDescription')->getData());
                $animal->setStatus('FOUND'); // El status va en la entidad Animals, no en FoundAnimals
                $animal->setCreatedAt(new \DateTimeImmutable());
                $animal->setUpdatedAt(new \DateTimeImmutable());

                $entityManager->persist($animal);

                // Procesar etiquetas
                $tagsInput = $form->get('animalTags')->getData();
                if ($tagsInput) {
                    $tagsRepository = $entityManager->getRepository(Tags::class);
                    $tagNames = array_map('trim', explode(',', $tagsInput));

                    foreach ($tagNames as $tagName) {
                        if (!empty($tagName)) {
                            $tag = $tagsRepository->findOrCreateByName($tagName);
                            $entityManager->persist($tag);

                            $animalTag = new AnimalTags();
                            $animalTag->setAnimalId($animal);
                            $animalTag->setTagId($tag);
                            $animalTag->setCreatedAt(new \DateTimeImmutable());

                            $entityManager->persist($animalTag);
                        }
                    }
                }

                // Procesar imagen del animal
                $photoFile = $form->get('animalPhoto')->getData();
                if ($photoFile) {
                    try {
                        $animalPhoto = $fileUploadService->uploadAnimalPhoto($photoFile, $animal, $user->getEmail());
                        $entityManager->persist($animalPhoto);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Error al procesar la imagen: ' . $e->getMessage());
                        throw $e; // Re-lanzar para que se maneje en el catch principal
                    }
                }

                // Crear el registro de animal encontrado
                $foundAnimal = new FoundAnimals();
                $foundAnimal->setAnimalId($animal);
                $foundAnimal->setUserId($user);
                $foundAnimal->setFoundDate($form->get('foundDate')->getData());
                $foundAnimal->setFoundTime($form->get('foundTime')->getData());
                $foundAnimal->setFoundZone($form->get('foundZone')->getData());
                $foundAnimal->setFoundAddress($form->get('foundAddress')->getData());
                $foundAnimal->setFoundCircumstances($form->get('foundCircumstances')->getData());
                $foundAnimal->setAdditionalNotes($form->get('additionalNotes')->getData());
                // Removido setStatus() ya que FoundAnimals no tiene este método
                $foundAnimal->setCreatedAt(new \DateTimeImmutable());
                $foundAnimal->setUpdatedAt(new \DateTimeImmutable());

                $entityManager->persist($foundAnimal);
                $entityManager->flush();

                $this->addFlash('success', 'Animal encontrado registrado exitosamente.');
                return $this->redirectToRoute('app_found_pets');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al registrar el animal encontrado: ' . $e->getMessage());
            }
        }

        return $this->render('found_pets/create.html.twig', [
            'controller_name' => 'FoundPetsController',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/found/pets/{id}/delete', name: 'app_found_pets_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
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
