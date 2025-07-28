<?php

namespace App\Controller;

use App\Entity\Animals;
use App\Entity\LostPets;
use App\Entity\Tags;
use App\Entity\AnimalTags;
use App\Form\LostPetType;
use App\Service\FileUploadService;
use App\Utils\ControllerUtils;
use App\Repository\LostPetsRepository;
use App\Repository\TagsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;

final class LostPetsController extends AbstractController
{
    #[Route('/lost/pets', name: 'app_lost_pets')]
    public function index(Request $request, LostPetsRepository $lostPetsRepository): Response
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
        error_log('Lost Pets Filters: ' . json_encode($filters));

        $lostPets = $lostPetsRepository->findAllWithRelationsPaginated($page, $limit, $filters);

        // Debug: log the count
        error_log('Lost Pets Found: ' . count($lostPets));

        if ($request->isXmlHttpRequest()) {
            return $this->render('lost_pets/_lost_animals_list.html.twig', [
                'lostPets' => $lostPets,
            ]);
        }

        return $this->render('lost_pets/index.html.twig', [
            'lostPets' => $lostPets,
            'filters' => $filters
        ]);
    }

    #[Route('/lost/pets/create', name: 'app_lost_pets_create')]
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

        $form = $this->createForm(LostPetType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Crear el animal
                $animal = new Animals();
                $animal->setName($form->get('animalName')->getData());
                $animal->setAnimalType($form->get('animalType')->getData());
                $animal->setGender($form->get('animalGender')->getData());
                $animal->setSize($form->get('animalSize')->getData());
                $animal->setColor($form->get('animalColor')->getData());
                $animal->setAge($form->get('animalAge')->getData());
                $animal->setDescription($form->get('animalDescription')->getData());
                $animal->setStatus('LOST');
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
                error_log('Photo files received: ' . (is_array($photoFiles) ? count($photoFiles) : 'not an array'));
                if (is_array($photoFiles)) {
                    foreach ($photoFiles as $index => $file) {
                        if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                            error_log("File $index: " . $file->getClientOriginalName() . " - Size: " . $file->getSize() . " - Valid: " . ($file->isValid() ? 'yes' : 'no'));
                        } else {
                            error_log("File $index: not an UploadedFile - " . gettype($file));
                        }
                    }
                }

                if ($photoFiles && !empty($photoFiles)) {
                    try {
                        $animalPhotos = $fileUploadService->uploadAnimalPhotos($photoFiles, $animal, $user->getEmail());
                        error_log('Photos uploaded: ' . count($animalPhotos));
                        foreach ($animalPhotos as $animalPhoto) {
                            $entityManager->persist($animalPhoto);
                        }
                    } catch (\Exception $e) {
                        error_log('Photo upload error: ' . $e->getMessage());
                        ControllerUtils::handleError(
                            fn($type, $message) => $this->addFlash($type, $message),
                            $e,
                            'Error al procesar las imágenes'
                        );
                        throw $e; // Re-lanzar para que se maneje en el catch principal
                    }
                } else {
                    error_log('No photo files to process');
                }

                // Crear el registro de animal perdido
                $lostPet = new LostPets();
                $lostPet->setAnimalId($animal);
                $lostPet->setUserId($user);
                $lostPet->setLostDate($form->get('lostDate')->getData());
                $lostPet->setLostTime($form->get('lostTime')->getData());
                $lostPet->setLostZone($form->get('lostZone')->getData());
                $lostPet->setLostAddress($form->get('lostAddress')->getData());
                $lostPet->setLostCircumstances($form->get('lostCircumstances')->getData());
                $lostPet->setRewardAmount($form->get('rewardAmount')->getData());
                $lostPet->setRewardDescription($form->get('rewardDescription')->getData());
                $lostPet->setCreatedAt(new \DateTimeImmutable());
                $lostPet->setUpdatedAt(new \DateTimeImmutable());

                $entityManager->persist($lostPet);
                $entityManager->flush();

                ControllerUtils::handleSuccess(
                    fn($type, $message) => $this->addFlash($type, $message),
                    'Animal perdido registrado exitosamente.'
                );
                return $this->redirectToRoute('app_lost_pets');
            } catch (\Exception $e) {
                ControllerUtils::handleError(
                    fn($type, $message) => $this->addFlash($type, $message),
                    $e,
                    'Error al registrar el animal perdido'
                );
            }
        }

        return $this->render('lost_pets/create.html.twig', [
            'controller_name' => 'LostPetsController',
            'form' => $form->createView(),
        ]);
    }
}
