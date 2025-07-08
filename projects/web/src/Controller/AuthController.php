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

            //TODO: Display flash messages
            if ($password !== $confirmPassword) {
                $this->addFlash('error', 'Las contraseñas no coinciden');
                return $this->redirectToRoute('app_auth');
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

            return $this->redirectToRoute('app_auth_register');
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

            // TODO: Create session and redirect to dashboard
            $this->addFlash('success', 'Inicio de sesión exitoso');
            return $this->redirectToRoute('app_auth_login');
        }

        return $this->render('auth/login.html.twig', [
            'controller_name' => 'AuthController',
        ]);
    }
}
