<?php

namespace App\Controller;

use App\Entity\Animals;
use App\Entity\LostPets;
use App\Entity\Tags;
use App\Entity\AnimalTags;
use App\Form\LostPetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LostPetsController extends AbstractController
{
    #[Route('/lost/pets', name: 'app_lost_pets')]
    public function index(): Response
    {
        return $this->render('lost_pets/index.html.twig', [
            'controller_name' => 'LostPetsController',
        ]);
    }

    #[Route('/lost/pets/create', name: 'app_lost_pets_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
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

                // Crear el registro de animal perdido
                $lostPet = new LostPets();
                $lostPet->setAnimalId($animal);
                $lostPet->setUserId($this->getUser());
                $lostPet->setLostDate($form->get('lostDate')->getData());
                $lostPet->setLostTime($form->get('lostTime')->getData());
                $lostPet->setLostZone($form->get('lostZone')->getData());
                $lostPet->setLostAddress($form->get('lostAddress')->getData());
                $lostPet->setLostCircumstances($form->get('lostCircumstances')->getData());
                $lostPet->setRewardAmount($form->get('rewardAmount')->getData());
                $lostPet->setRewardDescription($form->get('rewardDescription')->getData());
                $lostPet->setStatus('active');
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
}
