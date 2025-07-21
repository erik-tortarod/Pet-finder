<?php

namespace App\Controller;

use App\Entity\Animals;
use App\Entity\AnimalTags;
use App\Form\EditLostPetType;
use App\Form\EditFoundPetType;
use App\Service\FileUploadService;
use App\Repository\UserRepository;
use App\Repository\AnimalsRepository;
use App\Repository\LostPetsRepository;
use App\Repository\FoundAnimalsRepository;
use App\Repository\TagsRepository;
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
    public function show(int $id, AnimalsRepository $animalsRepository, LostPetsRepository $lostPetsRepository, FoundAnimalsRepository $foundAnimalsRepository): Response
    {
        // Buscar el animal con todas sus relaciones
        $animal = $animalsRepository->find($id);

        if (!$animal) {
            throw $this->createNotFoundException('Animal no encontrado');
        }

        // Buscar si es un animal perdido
        $lostPet = $lostPetsRepository->findByAnimal($animal);

        // Buscar si es un animal encontrado
        $foundAnimal = $foundAnimalsRepository->findByAnimal($animal);

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
    public function showBySlug(int $id, string $slug, AnimalsRepository $animalsRepository, LostPetsRepository $lostPetsRepository, FoundAnimalsRepository $foundAnimalsRepository): Response
    {
        // Buscar el animal por ID
        $animal = $animalsRepository->find($id);

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
        $lostPet = $lostPetsRepository->findByAnimal($animal);

        // Buscar si es un animal encontrado
        $foundAnimal = $foundAnimalsRepository->findByAnimal($animal);

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

    #[Route('/animal/edit/{id}', name: 'app_animal_edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request, AnimalsRepository $animalsRepository, LostPetsRepository $lostPetsRepository, FoundAnimalsRepository $foundAnimalsRepository, UserRepository $userRepository): Response
    {
        // Verificar que el usuario esté autenticado
        $user = ControllerUtils::requireAuthentication(
            $request,
            $userRepository,
            fn() => $this->getUser(),
            fn($type, $message) => $this->addFlash($type, $message),
            'Debes iniciar sesión para editar una publicación'
        );
        if (!$user) {
            return ControllerUtils::redirectToLogin(
                fn($type, $message) => $this->addFlash($type, $message),
                fn($route) => $this->redirectToRoute($route),
                'Debes iniciar sesión para editar una publicación'
            );
        }

        // Buscar el animal
        $animal = $animalsRepository->find($id);

        if (!$animal) {
            throw $this->createNotFoundException('Animal no encontrado');
        }

        // Buscar si es un animal perdido o encontrado
        $lostPet = $lostPetsRepository->findByAnimal($animal);
        $foundAnimal = $foundAnimalsRepository->findByAnimal($animal);

        if (!$lostPet && !$foundAnimal) {
            throw $this->createNotFoundException('Publicación no encontrada');
        }

        // Verificar que el usuario sea el propietario de la publicación
        $entityToCheck = $lostPet ?: $foundAnimal;
        if (!ControllerUtils::checkOwnership(
            $user,
            $entityToCheck->getUserId(),
            fn($type, $message) => $this->addFlash($type, $message),
            'No tienes permisos para editar esta publicación'
        )) {
            return $this->redirectToRoute('app_home');
        }

        // Crear el formulario apropiado con datos pre-llenados
        if ($lostPet) {
            $form = $this->createForm(EditLostPetType::class, $animal, [
                'lostPet' => $lostPet
            ]);

            // Establecer datos específicos de LostPets
            $form->get('animalType')->setData($animal->getAnimalType());
            $form->get('animalTags')->setData($this->getAnimalTagsAsString($animal));
            $form->get('lostDate')->setData($lostPet->getLostDate());
            $form->get('lostTime')->setData($lostPet->getLostTime());
            $form->get('lostZone')->setData($lostPet->getLostZone());
            $form->get('lostAddress')->setData($lostPet->getLostAddress());
            $form->get('lostCircumstances')->setData($lostPet->getLostCircumstances());
            $form->get('rewardAmount')->setData($lostPet->getRewardAmount());
            $form->get('rewardDescription')->setData($lostPet->getRewardDescription());
        } else {
            $form = $this->createForm(EditFoundPetType::class, $animal, [
                'foundAnimal' => $foundAnimal
            ]);

            // Establecer datos específicos de FoundAnimals
            $form->get('animalType')->setData($animal->getAnimalType());
            $form->get('animalTags')->setData($this->getAnimalTagsAsString($animal));
            $form->get('foundDate')->setData($foundAnimal->getFoundDate());
            $form->get('foundTime')->setData($foundAnimal->getFoundTime());
            $form->get('foundZone')->setData($foundAnimal->getFoundZone());
            $form->get('foundAddress')->setData($foundAnimal->getFoundAddress());
            $form->get('foundCircumstances')->setData($foundAnimal->getFoundCircumstances());
            $form->get('additionalNotes')->setData($foundAnimal->getAdditionalNotes());
        }

        return $this->render('animal/edit.html.twig', [
            'animal' => $animal,
            'lostPet' => $lostPet,
            'foundAnimal' => $foundAnimal,
            'form' => $form->createView(),
            'isLostPet' => $lostPet !== null,
        ]);
    }

    #[Route('/animal/edit/{id}', name: 'app_animal_update', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request, AnimalsRepository $animalsRepository, LostPetsRepository $lostPetsRepository, FoundAnimalsRepository $foundAnimalsRepository, FileUploadService $fileUploadService, UserRepository $userRepository, TagsRepository $tagsRepository, EntityManagerInterface $entityManager): Response
    {
        // Verificar que el usuario esté autenticado
        $user = ControllerUtils::requireAuthentication(
            $request,
            $userRepository,
            fn() => $this->getUser(),
            fn($type, $message) => $this->addFlash($type, $message),
            'Debes iniciar sesión para editar una publicación'
        );
        if (!$user) {
            return ControllerUtils::redirectToLogin(
                fn($type, $message) => $this->addFlash($type, $message),
                fn($route) => $this->redirectToRoute($route),
                'Debes iniciar sesión para editar una publicación'
            );
        }

        // Buscar el animal
        $animal = $animalsRepository->find($id);

        if (!$animal) {
            throw $this->createNotFoundException('Animal no encontrado');
        }

        // Buscar si es un animal perdido o encontrado
        $lostPet = $lostPetsRepository->findByAnimal($animal);
        $foundAnimal = $foundAnimalsRepository->findByAnimal($animal);

        if (!$lostPet && !$foundAnimal) {
            throw $this->createNotFoundException('Publicación no encontrada');
        }

        // Verificar que el usuario sea el propietario de la publicación
        $entityToCheck = $lostPet ?: $foundAnimal;
        if (!ControllerUtils::checkOwnership(
            $user,
            $entityToCheck->getUserId(),
            fn($type, $message) => $this->addFlash($type, $message),
            'No tienes permisos para editar esta publicación'
        )) {
            return $this->redirectToRoute('app_home');
        }

        // Crear el formulario apropiado
        if ($lostPet) {
            $form = $this->createForm(EditLostPetType::class, $animal, [
                'lostPet' => $lostPet
            ]);
        } else {
            $form = $this->createForm(EditFoundPetType::class, $animal, [
                'foundAnimal' => $foundAnimal
            ]);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Actualizar datos del animal (mapeados automáticamente)
                $animal->setAnimalType($form->get('animalType')->getData());
                $animal->setUpdatedAt(new \DateTimeImmutable());

                // Actualizar etiquetas
                $this->updateAnimalTags($animal, $form->get('animalTags')->getData(), $entityManager, $tagsRepository);

                // Procesar nueva imagen si se subió
                $photoFile = $form->get('animalPhoto')->getData();
                if ($photoFile) {
                    try {
                        $animalPhoto = $fileUploadService->uploadAnimalPhoto($photoFile, $animal, $user->getEmail());
                        $entityManager->persist($animalPhoto);
                    } catch (\Exception $e) {
                        ControllerUtils::handleError(
                            fn($type, $message) => $this->addFlash($type, $message),
                            $e,
                            'Error al procesar la imagen'
                        );
                        throw $e;
                    }
                }

                // Actualizar datos específicos según el tipo
                if ($lostPet) {
                    $lostPet->setLostDate($form->get('lostDate')->getData());
                    $lostPet->setLostTime($form->get('lostTime')->getData());
                    $lostPet->setLostZone($form->get('lostZone')->getData());
                    $lostPet->setLostAddress($form->get('lostAddress')->getData());
                    $lostPet->setLostCircumstances($form->get('lostCircumstances')->getData());
                    $lostPet->setRewardAmount($form->get('rewardAmount')->getData());
                    $lostPet->setRewardDescription($form->get('rewardDescription')->getData());
                    $lostPet->setUpdatedAt(new \DateTimeImmutable());
                } else {
                    $foundAnimal->setFoundDate($form->get('foundDate')->getData());
                    $foundAnimal->setFoundTime($form->get('foundTime')->getData());
                    $foundAnimal->setFoundZone($form->get('foundZone')->getData());
                    $foundAnimal->setFoundAddress($form->get('foundAddress')->getData());
                    $foundAnimal->setFoundCircumstances($form->get('foundCircumstances')->getData());
                    $foundAnimal->setAdditionalNotes($form->get('additionalNotes')->getData());
                    $foundAnimal->setUpdatedAt(new \DateTimeImmutable());
                }

                $entityManager->flush();

                ControllerUtils::handleSuccess(
                    fn($type, $message) => $this->addFlash($type, $message),
                    'Animal actualizado correctamente'
                );

                return $this->redirectToRoute('app_animal_show_slug', [
                    'id' => $animal->getId(),
                    'slug' => $animal->generateSlug()
                ]);
            } catch (\Exception $e) {
                ControllerUtils::handleError(
                    fn($type, $message) => $this->addFlash($type, $message),
                    $e,
                    'Error al actualizar el animal'
                );
            }
        }

        return $this->render('animal/edit.html.twig', [
            'animal' => $animal,
            'lostPet' => $lostPet,
            'foundAnimal' => $foundAnimal,
            'form' => $form->createView(),
            'isLostPet' => $lostPet !== null,
        ]);
    }

    #[Route('/animal/delete/{id}', name: 'app_animal_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function deleteAnimal(Request $request, int $id, AnimalsRepository $animalsRepository, LostPetsRepository $lostPetsRepository, FoundAnimalsRepository $foundAnimalsRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
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
        $animal = $animalsRepository->find($id);

        if (!$animal) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception('Animal no encontrado'),
                'Error'
            );
            return $this->redirectToRoute('app_home');
        }

        // Determinar si es un animal perdido o encontrado
        $lostPet = $lostPetsRepository->findByAnimal($animal);
        $foundAnimal = $foundAnimalsRepository->findByAnimal($animal);

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

    /**
     * Obtiene las etiquetas del animal como string separado por comas
     */
    private function getAnimalTagsAsString(Animals $animal): string
    {
        $tags = [];
        foreach ($animal->getAnimalTags() as $animalTag) {
            $tags[] = $animalTag->getTagId()->getName();
        }
        return implode(', ', $tags);
    }

    /**
     * Actualiza las etiquetas del animal
     */
    private function updateAnimalTags(Animals $animal, ?string $tagsInput, EntityManagerInterface $entityManager, TagsRepository $tagsRepository): void
    {
        // Eliminar etiquetas existentes
        foreach ($animal->getAnimalTags() as $animalTag) {
            $entityManager->remove($animalTag);
        }

        // Agregar nuevas etiquetas
        if ($tagsInput) {
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
    }
}
