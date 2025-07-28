<?php

namespace App\Controller;

use App\Repository\LostPetsRepository;
use App\Repository\FoundAnimalsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(LostPetsRepository $lostPetsRepository, FoundAnimalsRepository $foundAnimalsRepository): Response
    {
        // Get the last 3 lost pets with their related data
        $recentLostPets = $lostPetsRepository->findAllWithRelationsPaginated(1, 3);

        // Get the last 3 found animals with their related data
        $recentFoundAnimals = $foundAnimalsRepository->findAllWithRelationsPaginated(1, 3);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'recentLostPets' => $recentLostPets,
            'recentFoundAnimals' => $recentFoundAnimals,
        ]);
    }
}
