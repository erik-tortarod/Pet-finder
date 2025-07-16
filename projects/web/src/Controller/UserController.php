<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Repository\LostPetsRepository;
use App\Repository\FoundAnimalsRepository;
use Doctrine\ORM\EntityManagerInterface;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // Obtener información del usuario desde la sesión
        $session = $request->getSession();
        $userId = $session->get('user_id');

        if (!$userId) {
            $this->addFlash('error', 'Debes iniciar sesión para acceder a esta página');
            return $this->redirectToRoute('app_auth_login');
        }

        // Obtener el usuario completo desde la base de datos
        $user = $userRepository->find($userId);

        if (!$user) {
            $this->addFlash('error', 'Usuario no encontrado');
            $session->clear();
            $session->invalidate();
            return $this->redirectToRoute('app_auth_login');
        }

        // Verificar que el usuario esté activo
        if (!$user->isActive()) {
            $this->addFlash('error', 'Tu cuenta está desactivada');
            $session->clear();
            $session->invalidate();
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

        // Contar todas las publicaciones (el campo status no está mapeado en las entidades)
        $totalLostPets = count($lostPets);
        $totalFoundAnimals = count($foundAnimals);

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'lostPets' => $lostPets,
            'foundAnimals' => $foundAnimals,
            'activeLostPetsCount' => $totalLostPets,
            'activeFoundAnimalsCount' => $totalFoundAnimals,
            'controller_name' => 'UserController',
        ]);
    }
}
