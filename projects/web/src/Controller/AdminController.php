<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/shelters', name: 'app_admin_shelters')]
    #[IsGranted('ROLE_ADMIN')]
    public function shelters(Request $request, UserRepository $userRepository): Response
    {
        $filter = $request->query->get('filter', 'all');

        $shelters = $userRepository->findBy(['isShelter' => true], ['createdAt' => 'DESC']);

        if ($filter !== 'all') {
            $shelters = array_filter($shelters, function ($shelter) use ($filter) {
                $status = strtolower($shelter->getShelterVerificationStatus());
                return $status === $filter;
            });
        }

        return $this->render('admin/shelters.html.twig', [
            'shelters' => $shelters,
            'currentFilter' => $filter,
        ]);
    }
}
