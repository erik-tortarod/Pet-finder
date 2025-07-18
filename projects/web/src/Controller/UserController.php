<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
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
    public function userLostPets(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getAuthenticatedUser($request, $userRepository);
        if (!$user) {
            return $this->redirectToRoute('app_auth_login');
        }

        // Obtener todas las mascotas perdidas del usuario con toda la información
        $lostPetsRepository = $entityManager->getRepository('App\Entity\LostPets');
        $lostPets = $lostPetsRepository->createQueryBuilder('lp')
            ->leftJoin('lp.animalId', 'a')
            ->leftJoin('a.animalPhotos', 'ap')
            ->leftJoin('a.animalTags', 'at')
            ->leftJoin('at.tagId', 't')
            ->addSelect('a', 'ap', 'at', 't')
            ->where('lp.userId = :userId')
            ->setParameter('userId', $user)
            ->orderBy('lp.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        // Obtener estadísticas
        $stats = $this->getUserStats($entityManager, $user);

        return $this->render('user/lost_pets.html.twig', [
            'user' => $user,
            'lostPets' => $lostPets,
            'stats' => $stats,
            'activeTab' => 'lost_pets',
        ]);
    }

    #[Route('/user/found-pets', name: 'app_user_found_pets')]
    public function userFoundPets(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getAuthenticatedUser($request, $userRepository);
        if (!$user) {
            return $this->redirectToRoute('app_auth_login');
        }

        // Obtener todos los animales encontrados del usuario con toda la información
        $foundAnimalsRepository = $entityManager->getRepository('App\Entity\FoundAnimals');
        $foundAnimals = $foundAnimalsRepository->createQueryBuilder('fa')
            ->leftJoin('fa.animalId', 'a')
            ->leftJoin('a.animalPhotos', 'ap')
            ->leftJoin('a.animalTags', 'at')
            ->leftJoin('at.tagId', 't')
            ->addSelect('a', 'ap', 'at', 't')
            ->where('fa.userId = :userId')
            ->setParameter('userId', $user)
            ->orderBy('fa.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        // Obtener estadísticas
        $stats = $this->getUserStats($entityManager, $user);

        return $this->render('user/found_pets.html.twig', [
            'user' => $user,
            'foundAnimals' => $foundAnimals,
            'stats' => $stats,
            'activeTab' => 'found_pets',
        ]);
    }

    #[Route('/user/settings', name: 'app_user_settings')]
    public function userSettings(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getAuthenticatedUser($request, $userRepository);
        if (!$user) {
            return $this->redirectToRoute('app_auth_login');
        }

        // Obtener estadísticas
        $stats = $this->getUserStats($entityManager, $user);

        return $this->render('user/settings.html.twig', [
            'user' => $user,
            'stats' => $stats,
            'activeTab' => 'settings',
        ]);
    }

    /**
     * Helper method to get authenticated user
     */
    private function getAuthenticatedUser(Request $request, UserRepository $userRepository)
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');

        if (!$userId) {
            $this->addFlash('error', 'Debes iniciar sesión para acceder a esta página');
            return null;
        }

        $user = $userRepository->find($userId);

        if (!$user) {
            $this->addFlash('error', 'Usuario no encontrado');
            $session->clear();
            $session->invalidate();
            return null;
        }

        if (!$user->isActive()) {
            $this->addFlash('error', 'Tu cuenta está desactivada');
            $session->clear();
            $session->invalidate();
            return null;
        }

        return $user;
    }

    /**
     * Helper method to get user statistics
     */
    private function getUserStats(EntityManagerInterface $entityManager, $user): array
    {
        // Contar mascotas perdidas
        $lostPetsRepository = $entityManager->getRepository('App\Entity\LostPets');
        $totalLostPets = $lostPetsRepository->count(['userId' => $user]);

        // Contar animales encontrados
        $foundAnimalsRepository = $entityManager->getRepository('App\Entity\FoundAnimals');
        $totalFoundAnimals = $foundAnimalsRepository->count(['userId' => $user]);

        return [
            'totalLostPets' => $totalLostPets,
            'totalFoundAnimals' => $totalFoundAnimals,
            'totalPublications' => $totalLostPets + $totalFoundAnimals,
        ];
    }
}
