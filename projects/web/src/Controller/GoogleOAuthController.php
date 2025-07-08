<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;

final class GoogleOAuthController extends AbstractController
{
   public function __construct(
      private TokenStorageInterface $tokenStorage
   ) {}

   #[Route('/connect/google/check', name: 'connect_google_check')]
   public function connectGoogleCheck(Request $request): Response
   {
      // Obtener el usuario autenticado
      $user = $this->getUser();

      if (!$user || !$user instanceof User) {
         $this->addFlash('error', 'Error en la autenticación con Google');
         return $this->redirectToRoute('app_auth_login');
      }

      // Crear sesión manualmente
      $request->getSession()->set('user_id', $user->getId());
      $request->getSession()->set('user_email', $user->getEmail());
      $request->getSession()->set('user_first_name', $user->getFirstName());
      $request->getSession()->set('user_last_name', $user->getLastName());

      $this->addFlash('success', 'Inicio de sesión exitoso con Google');
      return $this->redirectToRoute('app_user');
   }
}
