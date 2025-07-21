<?php

namespace App\Utils;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ControllerUtils
{
    /**
     * Obtiene el usuario desde la sesión manual
     */
    public static function getUserFromSession(Request $request, UserRepository $userRepository, callable $getUserCallback): ?User
    {
        // Primero intentar con el sistema de seguridad de Symfony
        $user = $getUserCallback();
        if ($user) {
            return $user;
        }

        // Si no funciona, usar la sesión manual
        $session = $request->getSession();
        $userId = $session->get('user_id');

        if (!$userId) {
            return null;
        }

        return $userRepository->find($userId);
    }

    /**
     * Verifica si el usuario está autenticado y redirige si no lo está
     */
    public static function requireAuthentication(Request $request, UserRepository $userRepository, callable $getUserCallback, callable $addFlashCallback, string $errorMessage = 'Debes iniciar sesión para acceder a esta página'): ?User
    {
        $user = self::getUserFromSession($request, $userRepository, $getUserCallback);

        if (!$user) {
            $addFlashCallback('error', $errorMessage);
            return null;
        }

        return $user;
    }

    /**
     * Verifica si el usuario está autenticado con validaciones adicionales (activo, etc.)
     */
    public static function requireValidatedAuthentication(Request $request, UserRepository $userRepository, callable $addFlashCallback, string $errorMessage = 'Debes iniciar sesión para acceder a esta página'): ?User
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');

        if (!$userId) {
            $addFlashCallback('error', $errorMessage);
            return null;
        }

        $user = $userRepository->find($userId);

        if (!$user) {
            $addFlashCallback('error', 'Usuario no encontrado');
            $session->clear();
            $session->invalidate();
            return null;
        }

        if (!$user->isActive()) {
            $addFlashCallback('error', 'Tu cuenta está desactivada');
            $session->clear();
            $session->invalidate();
            return null;
        }

        return $user;
    }

    /**
     * Redirige al login con mensaje de error
     */
    public static function redirectToLogin(callable $addFlashCallback, callable $redirectCallback, string $errorMessage = 'Debes iniciar sesión para acceder a esta página'): RedirectResponse
    {
        $addFlashCallback('error', $errorMessage);
        return $redirectCallback('app_auth_login');
    }

    /**
     * Verifica si el usuario es propietario de un recurso
     */
    public static function checkOwnership(User $user, User $resourceOwner, callable $addFlashCallback, string $errorMessage = 'No tienes permisos para realizar esta acción'): bool
    {
        if ($resourceOwner !== $user) {
            $addFlashCallback('error', $errorMessage);
            return false;
        }

        return true;
    }

    /**
     * Maneja errores de forma consistente
     */
    public static function handleError(callable $addFlashCallback, \Exception $e, string $contextMessage = 'Error'): void
    {
        $addFlashCallback('error', $contextMessage . ': ' . $e->getMessage());
    }

    /**
     * Maneja éxitos de forma consistente
     */
    public static function handleSuccess(callable $addFlashCallback, string $message): void
    {
        $addFlashCallback('success', $message);
    }
}
