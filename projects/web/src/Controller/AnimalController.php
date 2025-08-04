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
use Symfony\Contracts\Translation\TranslatorInterface;

final class AnimalController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator
    ) {
        ControllerUtils::setTranslator($this->translator);
    }

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
            $this->translator->trans('flash.auth.login_required_edit')
        );
        if (!$user) {
            return ControllerUtils::redirectToLogin(
                fn($type, $message) => $this->addFlash($type, $message),
                fn($route) => $this->redirectToRoute($route),
                $this->translator->trans('flash.auth.login_required_edit')
            );
        }

        // Buscar el animal
        $animal = $animalsRepository->find($id);

        if (!$animal) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.not_found'))
            );
            return $this->redirectToRoute('app_home');
        }

        // Determinar si es un animal perdido o encontrado
        $lostPet = $lostPetsRepository->findByAnimal($animal);
        $foundAnimal = $foundAnimalsRepository->findByAnimal($animal);

        if (!$lostPet && !$foundAnimal) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.publication_not_found'))
            );
            return $this->redirectToRoute('app_home');
        }

        // Verificar que el usuario sea el propietario de la publicación
        $entityToCheck = $lostPet ?: $foundAnimal;
        if (!ControllerUtils::checkOwnership(
            $user,
            $entityToCheck->getUserId(),
            fn($type, $message) => $this->addFlash($type, $message),
            $this->translator->trans('flash.auth.no_permissions_edit')
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
            $this->translator->trans('flash.auth.login_required_edit')
        );
        if (!$user) {
            return ControllerUtils::redirectToLogin(
                fn($type, $message) => $this->addFlash($type, $message),
                fn($route) => $this->redirectToRoute($route),
                $this->translator->trans('flash.auth.login_required_edit')
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
            $this->translator->trans('flash.auth.no_permissions_edit')
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
                            $this->translator->trans('flash.animal.image_error')
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
                    $this->translator->trans('flash.animal.updated_success')
                );

                return $this->redirectToRoute('app_animal_show_slug', [
                    'id' => $animal->getId(),
                    'slug' => $animal->generateSlug()
                ]);
            } catch (\Exception $e) {
                ControllerUtils::handleError(
                    fn($type, $message) => $this->addFlash($type, $message),
                    $e,
                    $this->translator->trans('flash.animal.update_error')
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
            $this->translator->trans('flash.auth.login_required_delete')
        );
        if (!$user) {
            return ControllerUtils::redirectToLogin(
                fn($type, $message) => $this->addFlash($type, $message),
                fn($route) => $this->redirectToRoute($route),
                $this->translator->trans('flash.auth.login_required_delete')
            );
        }

        // Buscar el animal
        $animal = $animalsRepository->find($id);

        if (!$animal) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.not_found'))
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
            $this->translator->trans('flash.auth.no_permissions_delete')
        )) {
            return $this->redirectToRoute('app_home');
        }

        try {
            // Eliminar la entidad (esto también eliminará el animal asociado debido al cascade)
            $entityManager->remove($entityToDelete);
            $entityManager->flush();

            ControllerUtils::handleSuccess(
                fn($type, $message) => $this->addFlash($type, $message),
                $this->translator->trans('flash.animal.deleted_success')
            );
        } catch (\Exception $e) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                $e,
                $this->translator->trans('flash.animal.delete_error')
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

    #[Route('/animal/archive/{id}', name: 'app_animal_archive', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function archiveAnimal(Request $request, int $id, AnimalsRepository $animalsRepository, LostPetsRepository $lostPetsRepository, FoundAnimalsRepository $foundAnimalsRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // Verificar que el usuario esté autenticado
        $user = ControllerUtils::requireAuthentication(
            $request,
            $userRepository,
            fn() => $this->getUser(),
            fn($type, $message) => $this->addFlash($type, $message),
            $this->translator->trans('flash.auth.login_required_archive')
        );
        if (!$user) {
            return ControllerUtils::redirectToLogin(
                fn($type, $message) => $this->addFlash($type, $message),
                fn($route) => $this->redirectToRoute($route),
                $this->translator->trans('flash.auth.login_required_archive')
            );
        }

        // Buscar el animal
        $animal = $animalsRepository->find($id);

        if (!$animal) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.not_found'))
            );
            return $this->redirectToRoute('app_home');
        }

        // Determinar si es un animal perdido o encontrado
        $lostPet = $lostPetsRepository->findByAnimal($animal);
        $foundAnimal = $foundAnimalsRepository->findByAnimal($animal);

        if (!$lostPet && !$foundAnimal) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.publication_not_found'))
            );
            return $this->redirectToRoute('app_home');
        }

        // Verificar que el usuario sea el propietario de la publicación
        $entityToCheck = $lostPet ?: $foundAnimal;
        if (!ControllerUtils::checkOwnership(
            $user,
            $entityToCheck->getUserId(),
            fn($type, $message) => $this->addFlash($type, $message),
            $this->translator->trans('flash.auth.no_permissions_archive')
        )) {
            return $this->redirectToRoute('app_home');
        }

        try {
            // Cambiar el estado del animal a ARCHIVED
            $oldStatus = $animal->getStatus();
            $animal->setStatus('ARCHIVED');
            $animal->setUpdatedAt(new \DateTimeImmutable());

            // Asegurarse de que el animal esté siendo persistido
            $entityManager->persist($animal);
            $entityManager->flush();

            ControllerUtils::handleSuccess(
                fn($type, $message) => $this->addFlash($type, $message),
                $this->translator->trans('flash.animal.archived_success')
            );
        } catch (\Exception $e) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                $e,
                $this->translator->trans('flash.animal.archive_error')
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

    #[Route('/animal/restore/{id}', name: 'app_animal_restore', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function restoreAnimal(Request $request, int $id, AnimalsRepository $animalsRepository, LostPetsRepository $lostPetsRepository, FoundAnimalsRepository $foundAnimalsRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // Verificar que el usuario esté autenticado
        $user = ControllerUtils::requireAuthentication(
            $request,
            $userRepository,
            fn() => $this->getUser(),
            fn($type, $message) => $this->addFlash($type, $message),
            $this->translator->trans('flash.auth.login_required_restore')
        );
        if (!$user) {
            return ControllerUtils::redirectToLogin(
                fn($type, $message) => $this->addFlash($type, $message),
                fn($route) => $this->redirectToRoute($route),
                $this->translator->trans('flash.auth.login_required_restore')
            );
        }

        // Buscar el animal
        $animal = $animalsRepository->find($id);

        if (!$animal) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.not_found'))
            );
            return $this->redirectToRoute('app_home');
        }

        // Verificar que el animal esté archivado
        if ($animal->getStatus() !== 'ARCHIVED') {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.not_archived'))
            );
            return $this->redirectToRoute('app_home');
        }

        // Determinar si es un animal perdido o encontrado
        $lostPet = $lostPetsRepository->findByAnimal($animal);
        $foundAnimal = $foundAnimalsRepository->findByAnimal($animal);

        if (!$lostPet && !$foundAnimal) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.publication_not_found'))
            );
            return $this->redirectToRoute('app_home');
        }

        // Verificar que el usuario sea el propietario de la publicación
        $entityToCheck = $lostPet ?: $foundAnimal;
        if (!ControllerUtils::checkOwnership(
            $user,
            $entityToCheck->getUserId(),
            fn($type, $message) => $this->addFlash($type, $message),
            $this->translator->trans('flash.auth.no_permissions_restore')
        )) {
            return $this->redirectToRoute('app_home');
        }

        try {
            // Restaurar el estado del animal según su tipo original
            $originalStatus = $lostPet ? 'LOST' : 'FOUND';
            $animal->setStatus($originalStatus);
            $animal->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            ControllerUtils::handleSuccess(
                fn($type, $message) => $this->addFlash($type, $message),
                $this->translator->trans('flash.animal.restored_success')
            );
        } catch (\Exception $e) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                $e,
                $this->translator->trans('flash.animal.restore_error')
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

    #[Route('/animal/mark-as-found/{id}', name: 'app_animal_mark_as_found', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function markAsFound(Request $request, int $id, AnimalsRepository $animalsRepository, LostPetsRepository $lostPetsRepository, FoundAnimalsRepository $foundAnimalsRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // Verificar que el usuario esté autenticado
        $user = ControllerUtils::requireAuthentication(
            $request,
            $userRepository,
            fn() => $this->getUser(),
            fn($type, $message) => $this->addFlash($type, $message),
            $this->translator->trans('flash.auth.login_required_mark_found')
        );
        if (!$user) {
            return ControllerUtils::redirectToLogin(
                fn($type, $message) => $this->addFlash($type, $message),
                fn($route) => $this->redirectToRoute($route),
                $this->translator->trans('flash.auth.login_required_mark_found')
            );
        }

        // Buscar el animal
        $animal = $animalsRepository->find($id);

        if (!$animal) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.not_found'))
            );
            return $this->redirectToRoute('app_home');
        }

        // Verificar que el animal no esté archivado
        if ($animal->getStatus() === 'ARCHIVED') {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.cannot_mark_archived_found'))
            );
            return $this->redirectToRoute('app_home');
        }

        // Verificar que el animal no esté ya reclamado
        if ($animal->getStatus() === 'CLAIMED') {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.already_claimed'))
            );
            return $this->redirectToRoute('app_home');
        }

        // Determinar si es un animal perdido o encontrado
        $lostPet = $lostPetsRepository->findByAnimal($animal);
        $foundAnimal = $foundAnimalsRepository->findByAnimal($animal);

        if (!$lostPet && !$foundAnimal) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.publication_not_found'))
            );
            return $this->redirectToRoute('app_home');
        }

        // Verificar que el usuario sea el propietario de la publicación
        $entityToCheck = $lostPet ?: $foundAnimal;
        if (!ControllerUtils::checkOwnership(
            $user,
            $entityToCheck->getUserId(),
            fn($type, $message) => $this->addFlash($type, $message),
            $this->translator->trans('flash.auth.no_permissions_mark_found')
        )) {
            return $this->redirectToRoute('app_home');
        }

        try {
            // Marcar el animal como reclamado
            $animal->setStatus('CLAIMED');
            $animal->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            ControllerUtils::handleSuccess(
                fn($type, $message) => $this->addFlash($type, $message),
                $this->translator->trans('flash.animal.marked_as_found_success')
            );
        } catch (\Exception $e) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                $e,
                $this->translator->trans('flash.animal.mark_as_found_error')
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

    #[Route('/animal/mark-under-protection/{id}', name: 'app_animal_mark_under_protection', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function markUnderProtection(Request $request, int $id, AnimalsRepository $animalsRepository, LostPetsRepository $lostPetsRepository, FoundAnimalsRepository $foundAnimalsRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // Verificar que el usuario esté autenticado
        $user = ControllerUtils::requireAuthentication(
            $request,
            $userRepository,
            fn() => $this->getUser(),
            fn($type, $message) => $this->addFlash($type, $message),
            $this->translator->trans('flash.auth.login_required_under_protection')
        );
        if (!$user) {
            return ControllerUtils::redirectToLogin(
                fn($type, $message) => $this->addFlash($type, $message),
                fn($route) => $this->redirectToRoute($route),
                $this->translator->trans('flash.auth.login_required_under_protection')
            );
        }

        // Verificar que el usuario sea una protectora
        if (!$user->isShelter()) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.auth.shelter_required'))
            );
            return $this->redirectToRoute('app_home');
        }

        // Buscar el animal
        $animal = $animalsRepository->find($id);

        if (!$animal) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.not_found'))
            );
            return $this->redirectToRoute('app_home');
        }

        // Verificar que el animal no esté archivado
        if ($animal->getStatus() === 'ARCHIVED') {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.cannot_mark_archived_under_protection'))
            );
            return $this->redirectToRoute('app_home');
        }

        // Verificar que el animal no esté ya reclamado
        if ($animal->getStatus() === 'CLAIMED') {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.already_claimed'))
            );
            return $this->redirectToRoute('app_home');
        }

        // Verificar que el animal no esté ya bajo protección
        if ($animal->getStatus() === 'UNDER_PROTECTION') {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.already_under_protection'))
            );
            return $this->redirectToRoute('app_home');
        }

        // Determinar si es un animal perdido o encontrado
        $lostPet = $lostPetsRepository->findByAnimal($animal);
        $foundAnimal = $foundAnimalsRepository->findByAnimal($animal);

        if (!$lostPet && !$foundAnimal) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.publication_not_found'))
            );
            return $this->redirectToRoute('app_home');
        }

        // Verificar que el usuario sea el propietario de la publicación
        $entityToCheck = $lostPet ?: $foundAnimal;
        if (!ControllerUtils::checkOwnership(
            $user,
            $entityToCheck->getUserId(),
            fn($type, $message) => $this->addFlash($type, $message),
            $this->translator->trans('flash.auth.no_permissions_under_protection')
        )) {
            return $this->redirectToRoute('app_home');
        }

        try {
            // Marcar el animal como bajo protección
            $animal->setStatus('UNDER_PROTECTION');
            $animal->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            ControllerUtils::handleSuccess(
                fn($type, $message) => $this->addFlash($type, $message),
                $this->translator->trans('flash.animal.marked_under_protection_success')
            );
        } catch (\Exception $e) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                $e,
                $this->translator->trans('flash.animal.mark_under_protection_error')
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

    #[Route('/animal/remove-under-protection/{id}', name: 'app_animal_remove_under_protection', methods: ['POST'], requirements: ["id" => "\d+"])]
    public function removeUnderProtection(Request $request, int $id, AnimalsRepository $animalsRepository, LostPetsRepository $lostPetsRepository, FoundAnimalsRepository $foundAnimalsRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // Verificar autenticación
        $user = ControllerUtils::requireAuthentication(
            $request,
            $userRepository,
            fn() => $this->getUser(),
            fn($type, $message) => $this->addFlash($type, $message),
            $this->translator->trans('flash.auth.login_required_under_protection')
        );
        if (!$user) {
            return ControllerUtils::redirectToLogin(
                fn($type, $message) => $this->addFlash($type, $message),
                fn($route) => $this->redirectToRoute($route),
                $this->translator->trans('flash.auth.login_required_under_protection')
            );
        }
        if (!$user->isShelter()) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.auth.shelter_required'))
            );
            return $this->redirectToRoute('app_home');
        }
        $animal = $animalsRepository->find($id);
        if (!$animal) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.not_found'))
            );
            return $this->redirectToRoute('app_home');
        }
        if ($animal->getStatus() !== 'UNDER_PROTECTION') {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.not_under_protection'))
            );
            return $this->redirectToRoute('app_home');
        }
        $lostPet = $lostPetsRepository->findByAnimal($animal);
        $foundAnimal = $foundAnimalsRepository->findByAnimal($animal);
        if (!$lostPet && !$foundAnimal) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                new \Exception($this->translator->trans('flash.animal.publication_not_found'))
            );
            return $this->redirectToRoute('app_home');
        }
        $entityToCheck = $lostPet ?: $foundAnimal;
        if (!ControllerUtils::checkOwnership(
            $user,
            $entityToCheck->getUserId(),
            fn($type, $message) => $this->addFlash($type, $message),
            $this->translator->trans('flash.auth.no_permissions_under_protection')
        )) {
            return $this->redirectToRoute('app_home');
        }
        try {
            if ($lostPet) {
                $animal->setStatus('LOST');
            } elseif ($foundAnimal) {
                $animal->setStatus('FOUND');
            }
            $animal->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();
            ControllerUtils::handleSuccess(
                fn($type, $message) => $this->addFlash($type, $message),
                $this->translator->trans('flash.animal.removed_under_protection_success')
            );
        } catch (\Exception $e) {
            ControllerUtils::handleError(
                fn($type, $message) => $this->addFlash($type, $message),
                $e,
                $this->translator->trans('flash.animal.remove_under_protection_error')
            );
        }
        // Redirigir de vuelta
        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }
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
