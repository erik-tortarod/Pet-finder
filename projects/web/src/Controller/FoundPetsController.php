<?php

namespace App\Controller;

use App\Entity\Animals;
use App\Entity\FoundAnimals;
use App\Entity\AnimalTags;
use App\Form\FoundPetType;
use App\Service\FileUploadService;
use App\Utils\ControllerUtils;
use App\Repository\FoundAnimalsRepository;
use App\Repository\TagsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;

final class FoundPetsController extends AbstractController
{
    #[Route('/found/pets', name: 'app_found_pets')]
    public function index(Request $request, FoundAnimalsRepository $foundAnimalsRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 9; // Show more results per page

        // Get filter parameters - don't filter empty values here
        $filters = [
            'search' => $request->query->get('search', ''),
            'animalType' => $request->query->get('animalType', ''),
            'zone' => $request->query->get('zone', ''),
            'tags' => $request->query->get('tags', '') ? explode(',', $request->query->get('tags')) : []
        ];

        // Debug: log the filters
        error_log('Found Pets Filters: ' . json_encode($filters));

        $foundAnimals = $foundAnimalsRepository->findAllWithRelationsPaginated($page, $limit, $filters);

        // Debug: log the count
        error_log('Found Pets Found: ' . count($foundAnimals));

        // Check if there are more items to load
        $hasMore = count($foundAnimals) === $limit;

        if ($request->isXmlHttpRequest()) {
            return $this->render('found_pets/_found_animals_list.html.twig', [
                'foundAnimals' => $foundAnimals,
            ]);
        }

        return $this->render('found_pets/index.html.twig', [
            'foundAnimals' => $foundAnimals,
            'filters' => $filters,
            'hasMore' => $hasMore,
            'currentPage' => $page
        ]);
    }


    #[Route('/found/pets/create', name: 'app_found_pets_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, FileUploadService $fileUploadService, UserRepository $userRepository, TagsRepository $tagsRepository): Response
    {
        // Verificar que el usuario esté autenticado usando sesión manual
        $user = ControllerUtils::requireAuthentication(
            $request,
            $userRepository,
            fn() => $this->getUser(),
            fn($type, $message) => $this->addFlash($type, $message),
            'Debes iniciar sesión para crear una publicación'
        );
        if (!$user) {
            return ControllerUtils::redirectToLogin(
                fn($type, $message) => $this->addFlash($type, $message),
                fn($route) => $this->redirectToRoute($route),
                'Debes iniciar sesión para crear una publicación'
            );
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
                $photoFiles = $form->get('animalPhoto')->getData();

                // Debug: Log what we're receiving
                error_log('Found Pets - Photo files received: ' . (is_array($photoFiles) ? count($photoFiles) : 'not an array'));
                if (is_array($photoFiles)) {
                    foreach ($photoFiles as $index => $file) {
                        if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                            error_log("Found Pets - File $index: " . $file->getClientOriginalName() . " - Size: " . $file->getSize() . " - Valid: " . ($file->isValid() ? 'yes' : 'no'));
                        } else {
                            error_log("Found Pets - File $index: not an UploadedFile - " . gettype($file));
                        }
                    }
                }

                if ($photoFiles && !empty($photoFiles)) {
                    try {
                        $animalPhotos = $fileUploadService->uploadAnimalPhotos($photoFiles, $animal, $user->getEmail());
                        error_log('Found Pets - Photos uploaded: ' . count($animalPhotos));
                        foreach ($animalPhotos as $animalPhoto) {
                            $entityManager->persist($animalPhoto);
                        }
                    } catch (\Exception $e) {
                        error_log('Found Pets - Photo upload error: ' . $e->getMessage());
                        ControllerUtils::handleError(
                            fn($type, $message) => $this->addFlash($type, $message),
                            $e,
                            'Error al procesar las imágenes'
                        );
                        throw $e; // Re-lanzar para que se maneje en el catch principal
                    }
                } else {
                    error_log('Found Pets - No photo files to process');
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

                ControllerUtils::handleSuccess(
                    fn($type, $message) => $this->addFlash($type, $message),
                    'Animal encontrado registrado exitosamente.'
                );
                return $this->redirectToRoute('app_found_pets');
            } catch (\Exception $e) {
                ControllerUtils::handleError(
                    fn($type, $message) => $this->addFlash($type, $message),
                    $e,
                    'Error al registrar el animal encontrado'
                );
            }
        }

        return $this->render('found_pets/create.html.twig', [
            'controller_name' => 'FoundPetsController',
            'form' => $form->createView(),
        ]);
    }
}
