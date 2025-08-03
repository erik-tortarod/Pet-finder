<?php

namespace App\Utils;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class ControllerUtils
{
    private static ?TranslatorInterface $translator = null;

    public static function setTranslator(TranslatorInterface $translator): void
    {
        self::$translator = $translator;
    }

    private static function trans(string $key): string
    {
        if (self::$translator) {
            return self::$translator->trans($key);
        }
        return $key;
    }

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
    public static function requireAuthentication(Request $request, UserRepository $userRepository, callable $getUserCallback, callable $addFlashCallback, string $errorMessage = null): ?User
    {
        $user = self::getUserFromSession($request, $userRepository, $getUserCallback);

        if (!$user) {
            $message = $errorMessage ?: self::trans('flash.auth.login_required');
            $addFlashCallback('error', $message);
            return null;
        }

        return $user;
    }

    /**
     * Verifica si el usuario está autenticado con validaciones adicionales (activo, etc.)
     */
    public static function requireValidatedAuthentication(Request $request, UserRepository $userRepository, callable $addFlashCallback, string $errorMessage = null): ?User
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');

        if (!$userId) {
            $message = $errorMessage ?: self::trans('flash.auth.login_required');
            $addFlashCallback('error', $message);
            return null;
        }

        $user = $userRepository->find($userId);

        if (!$user) {
            $addFlashCallback('error', self::trans('flash.auth.user_not_found'));
            $session->clear();
            $session->invalidate();
            return null;
        }

        if (!$user->isActive()) {
            $addFlashCallback('error', self::trans('flash.auth.account_disabled'));
            $session->clear();
            $session->invalidate();
            return null;
        }

        return $user;
    }

    /**
     * Redirige al login con mensaje de error
     */
    public static function redirectToLogin(callable $addFlashCallback, callable $redirectCallback, string $errorMessage = null): RedirectResponse
    {
        $message = $errorMessage ?: self::trans('flash.auth.login_required');
        $addFlashCallback('error', $message);
        return $redirectCallback('app_auth_login');
    }

    /**
     * Verifica si el usuario es propietario de un recurso
     */
    public static function checkOwnership(User $user, User $resourceOwner, callable $addFlashCallback, string $errorMessage = null): bool
    {
        if ($resourceOwner !== $user) {
            $message = $errorMessage ?: self::trans('flash.auth.no_permissions');
            $addFlashCallback('error', $message);
            return false;
        }

        return true;
    }

    /**
     * Maneja errores de forma consistente
     */
    public static function handleError(callable $addFlashCallback, \Exception $e, string $contextMessage = null): void
    {
        $message = $contextMessage ?: self::trans('common.error');
        $addFlashCallback('error', $message . ': ' . $e->getMessage());
    }

    /**
     * Maneja éxitos de forma consistente
     */
    public static function handleSuccess(callable $addFlashCallback, string $message): void
    {
        $addFlashCallback('success', $message);
    }
}
