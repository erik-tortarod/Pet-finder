<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\PasswordResetService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AuthController extends AbstractController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private PasswordResetService $passwordResetService
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

    #[Route('/auth/forgot-password', name: 'app_auth_forgot_password')]
    public function forgotPassword(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            if (empty($email)) {
                $this->addFlash('error', 'Por favor ingresa tu email');
                return $this->redirectToRoute('app_auth_forgot_password');
            }

            $success = $this->passwordResetService->requestPasswordReset($email);

            if ($success) {
                $this->addFlash('success', 'Se ha enviado un enlace de restablecimiento a tu email');
            } else {
                $this->addFlash('error', 'No se encontró una cuenta con ese email');
            }

            return $this->redirectToRoute('app_auth_forgot_password');
        }

        return $this->render('auth/forgot_password.html.twig', [
            'controller_name' => 'AuthController',
        ]);
    }

    #[Route('/auth/reset-password/{token}', name: 'app_auth_reset_password')]
    public function resetPassword(Request $request, string $token): Response
    {
        // Validar el token
        $user = $this->passwordResetService->validateToken($token);

        if (!$user) {
            $this->addFlash('error', 'El enlace de restablecimiento no es válido o ha expirado');
            return $this->redirectToRoute('app_auth_login');
        }

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');

            if (empty($password)) {
                $this->addFlash('error', 'La contraseña es requerida');
                return $this->redirectToRoute('app_auth_reset_password', ['token' => $token]);
            }

            if ($password !== $confirmPassword) {
                $this->addFlash('error', 'Las contraseñas no coinciden');
                return $this->redirectToRoute('app_auth_reset_password', ['token' => $token]);
            }

            if (strlen($password) < 6) {
                $this->addFlash('error', 'La contraseña debe tener al menos 6 caracteres');
                return $this->redirectToRoute('app_auth_reset_password', ['token' => $token]);
            }

            // Hash the password before setting it
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);

            $success = $this->passwordResetService->resetPassword($token, $hashedPassword);

            if ($success) {
                $this->addFlash('success', 'Tu contraseña ha sido restablecida exitosamente. Puedes iniciar sesión con tu nueva contraseña.');
                return $this->redirectToRoute('app_auth_login');
            } else {
                $this->addFlash('error', 'Error al restablecer la contraseña. El enlace puede haber expirado.');
                return $this->redirectToRoute('app_auth_login');
            }
        }

        return $this->render('auth/reset_password.html.twig', [
            'controller_name' => 'AuthController',
            'token' => $token,
        ]);
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
