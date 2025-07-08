<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(Request $request, UserRepository $userRepository): Response
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

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'controller_name' => 'UserController',
        ]);
    }
}
