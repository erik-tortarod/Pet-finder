<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AuthController extends AbstractController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('/auth/register', name: 'app_auth_register')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        if ($request->isMethod('POST')) {
            $firstName = $request->request->get('firstName');
            $lastName = $request->request->get('lastName');
            $phone = $request->request->get('phone');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');
            $emailNotifications = $request->request->get('emailNotifications');

            // Validaciones básicas
            if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
                $this->addFlash('error', 'Todos los campos obligatorios deben estar completos');
                return $this->redirectToRoute('app_auth_register');
            }

            if ($password !== $confirmPassword) {
                $this->addFlash('error', 'Las contraseñas no coinciden');
                return $this->redirectToRoute('app_auth_register');
            }

            // Verificar si el usuario ya existe
            $existingUser = $userRepository->findOneBy(['email' => $email]);
            if ($existingUser) {
                $this->addFlash('error', 'Ya existe un usuario con este email');
                return $this->redirectToRoute('app_auth_register');
            }

            $user = new User();
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setPhone($phone);
            $user->setEmail($email);

            // Hash the password before setting it
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setUpdatedAt(new \DateTimeImmutable());
            $user->setRoles(['ROLE_USER']);
            $user->setIsActive(true);
            $user->setLastLogin(new \DateTime());
            $user->setEmailNotifications($emailNotifications == "on" ? true : false);
            $user->setIsShelter(false);

            $userRepository->add($user, true);

            $this->addFlash('success', 'Usuario registrado exitosamente');
            return $this->redirectToRoute('app_user');
        }

        return $this->render('auth/register.html.twig', [
            'controller_name' => 'AuthController',
        ]);
    }

    #[Route('/auth/login', name: 'app_auth_login')]
    public function login(Request $request, UserRepository $userRepository): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');

            // Validaciones básicas
            if (empty($email) || empty($password)) {
                $this->addFlash('error', 'Email y contraseña son requeridos');
                return $this->redirectToRoute('app_auth_login');
            }

            $user = $userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('error', 'Usuario no encontrado');
                return $this->redirectToRoute('app_auth_login');
            }

            // Verify the password
            if (!$this->passwordHasher->isPasswordValid($user, $password)) {
                $this->addFlash('error', 'Contraseña incorrecta');
                return $this->redirectToRoute('app_auth_login');
            }

            // Update last login
            $user->setLastLogin(new \DateTime());
            $userRepository->add($user, true);

            // Crear sesión manualmente
            $request->getSession()->set('user_id', $user->getId());
            $request->getSession()->set('user_email', $user->getEmail());
            $request->getSession()->set('user_first_name', $user->getFirstName());
            $request->getSession()->set('user_last_name', $user->getLastName());

            $this->addFlash('success', 'Inicio de sesión exitoso');
            return $this->redirectToRoute('app_user');
        }

        return $this->render('auth/login.html.twig', [
            'controller_name' => 'AuthController',
        ]);
    }

    #[Route('/auth/logout', name: 'app_auth_logout')]
    public function logout(Request $request): Response
    {
        // Limpiar la sesión completamente
        $session = $request->getSession();

        // Limpiar todos los datos de la sesión
        $session->clear();

        // Invalidar la sesión
        $session->invalidate();

        // Regenerar el ID de la sesión para mayor seguridad
        $session->migrate();

        $this->addFlash('success', 'Sesión cerrada exitosamente');
        return $this->redirectToRoute('app_auth_login');
    }

    #[Route('/auth/debug-session', name: 'app_auth_debug_session')]
    public function debugSession(Request $request): Response
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');
        $userEmail = $session->get('user_email');

        return new Response(
            "Session Debug:<br>" .
                "User ID: " . ($userId ?: 'null') . "<br>" .
                "User Email: " . ($userEmail ?: 'null') . "<br>" .
                "Session ID: " . $session->getId() . "<br>" .
                "Session Started: " . ($session->isStarted() ? 'Yes' : 'No')
        );
    }
}
