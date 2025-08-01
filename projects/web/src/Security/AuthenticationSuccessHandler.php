<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $user = $token->getUser();

        if ($user instanceof User && $user->isShelter()) {
            $verificationStatus = $user->getShelterVerificationStatus();

            // Si el shelter no está verificado, redirigir a la página de verificación pendiente
            if ($verificationStatus === 'pending') {
                return new RedirectResponse($this->urlGenerator->generate('app_shelter_pending_verification'));
            }

            // Si el shelter fue rechazado, redirigir a la página de rechazo
            if ($verificationStatus === 'rejected') {
                return new RedirectResponse($this->urlGenerator->generate('app_shelter_rejected'));
            }
        }

        // Si es un usuario normal o un shelter verificado, redirigir al dashboard
        return new RedirectResponse($this->urlGenerator->generate('app_user'));
    }
}
