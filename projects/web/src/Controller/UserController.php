<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Repository\LostPetsRepository;
use App\Repository\FoundAnimalsRepository;
use App\Utils\ControllerUtils;
use Doctrine\ORM\EntityManagerInterface;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        // Redirect to the main user dashboard with lost pets tab active
        return $this->redirectToRoute('app_user_lost_pets');
    }

    #[Route('/user/lost-pets', name: 'app_user_lost_pets')]
    public function userLostPets(Request $request, UserRepository $userRepository, LostPetsRepository $lostPetsRepository): Response
    {
        $user = ControllerUtils::requireValidatedAuthentication(
            $request,
            $userRepository,
            fn($type, $message) => $this->addFlash($type, $message)
        );
        if (!$user) {
            return ControllerUtils::redirectToLogin(
                fn($type, $message) => $this->addFlash($type, $message),
                fn($route) => $this->redirectToRoute($route)
            );
        }

        // Obtener todas las mascotas perdidas del usuario con toda la información
        $lostPets = $lostPetsRepository->findByUserWithRelations($user);

        // Obtener estadísticas
        $stats = $this->getUserStats($lostPetsRepository, $user);

        return $this->render('user/lost_pets.html.twig', [
            'user' => $user,
            'lostPets' => $lostPets,
            'stats' => $stats,
            'activeTab' => 'lost_pets',
        ]);
    }

    #[Route('/user/found-pets', name: 'app_user_found_pets')]
    public function userFoundPets(Request $request, UserRepository $userRepository, FoundAnimalsRepository $foundAnimalsRepository): Response
    {
        $user = ControllerUtils::requireValidatedAuthentication(
            $request,
            $userRepository,
            fn($type, $message) => $this->addFlash($type, $message)
        );
        if (!$user) {
            return ControllerUtils::redirectToLogin(
                fn($type, $message) => $this->addFlash($type, $message),
                fn($route) => $this->redirectToRoute($route)
            );
        }

        // Obtener todos los animales encontrados del usuario con toda la información
        $foundAnimals = $foundAnimalsRepository->findByUserWithRelations($user);

        // Obtener estadísticas
        $stats = $this->getUserStats($foundAnimalsRepository, $user);

        return $this->render('user/found_pets.html.twig', [
            'user' => $user,
            'foundAnimals' => $foundAnimals,
            'stats' => $stats,
            'activeTab' => 'found_pets',
        ]);
    }

    #[Route('/user/settings', name: 'app_user_settings')]
    public function userSettings(Request $request, UserRepository $userRepository, LostPetsRepository $lostPetsRepository, FoundAnimalsRepository $foundAnimalsRepository): Response
    {
        $user = ControllerUtils::requireValidatedAuthentication(
            $request,
            $userRepository,
            fn($type, $message) => $this->addFlash($type, $message)
        );
        if (!$user) {
            return ControllerUtils::redirectToLogin(
                fn($type, $message) => $this->addFlash($type, $message),
                fn($route) => $this->redirectToRoute($route)
            );
        }

        // Obtener estadísticas
        $stats = $this->getUserStats($lostPetsRepository, $foundAnimalsRepository, $user);

        return $this->render('user/settings.html.twig', [
            'user' => $user,
            'stats' => $stats,
            'activeTab' => 'settings',
        ]);
    }

    /**
     * Helper method to get user statistics
     */
    private function getUserStats($lostPetsRepository = null, $foundAnimalsRepository = null, $user = null): array
    {
        // If we have both repositories and user, use them directly
        if ($lostPetsRepository && $foundAnimalsRepository && $user) {
            $totalLostPets = $lostPetsRepository->countByUser($user);
            $totalFoundAnimals = $foundAnimalsRepository->countByUser($user);
        } else {
            // Fallback for backward compatibility
            $totalLostPets = 0;
            $totalFoundAnimals = 0;
        }

        return [
            'totalLostPets' => $totalLostPets,
            'totalFoundAnimals' => $totalFoundAnimals,
            'totalPublications' => $totalLostPets + $totalFoundAnimals,
        ];
    }
}
