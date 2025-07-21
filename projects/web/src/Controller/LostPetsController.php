<?php

namespace App\Controller;

use App\Entity\Animals;
use App\Entity\LostPets;
use App\Entity\Tags;
use App\Entity\AnimalTags;
use App\Entity\AnimalPhotos;
use App\Form\LostPetType;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\UserRepository;

final class LostPetsController extends AbstractController
{
    #[Route('/lost/pets', name: 'app_lost_pets')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $lostPetsRepository = $entityManager->getRepository(LostPets::class);
        $lostPets = $lostPetsRepository->createQueryBuilder('lp')
            ->leftJoin('lp.animalId', 'a')
            ->leftJoin('a.animalPhotos', 'ap')
            ->leftJoin('a.animalTags', 'at')
            ->leftJoin('at.tagId', 't')
            ->leftJoin('lp.userId', 'u')
            ->addSelect('a', 'ap', 'at', 't', 'u')
            ->orderBy('lp.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('lost_pets/index.html.twig', [
            'lostPets' => $lostPets,
        ]);
    }

    #[Route('/lost/pets/create', name: 'app_lost_pets_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, FileUploadService $fileUploadService, UserRepository $userRepository): Response
    {
        // Verificar que el usuario esté autenticado usando sesión manual
        $user = $this->getUserFromSession($request, $userRepository);
        if (!$user) {
            $this->addFlash('error', 'Debes iniciar sesión para crear una publicación');
            return $this->redirectToRoute('app_auth_login');
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

                $this->addFlash('success', 'Animal perdido registrado exitosamente.');
                return $this->redirectToRoute('app_lost_pets');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al registrar el animal perdido: ' . $e->getMessage());
            }
        }

        return $this->render('lost_pets/create.html.twig', [
            'controller_name' => 'LostPetsController',
            'form' => $form->createView(),
        ]);
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
