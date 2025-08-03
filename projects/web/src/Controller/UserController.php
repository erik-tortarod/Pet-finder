<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\LostPetsRepository;
use App\Repository\FoundAnimalsRepository;
use App\Form\UserProfileUpdateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @method User getUser()
 */
final class UserController extends AbstractController
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    #[Route('/user', name: 'app_user')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        // Redirect to the main user dashboard with lost pets tab active
        return $this->redirectToRoute('app_user_lost_pets');
    }

    #[Route('/user/lost-pets', name: 'app_user_lost_pets')]
    #[IsGranted('ROLE_USER')]
    public function userLostPets(LostPetsRepository $lostPetsRepository, FoundAnimalsRepository $foundAnimalsRepository): Response
    {
        $user = $this->getUser();

        // Obtener todas las mascotas perdidas del usuario con toda la información
        $lostPets = $lostPetsRepository->findByUserWithRelations($user);

        // Obtener estadísticas
        $stats = $this->getUserStats($lostPetsRepository, $foundAnimalsRepository, $user);

        return $this->render('user/lost_pets.html.twig', [
            'user' => $user,
            'lostPets' => $lostPets,
            'stats' => $stats,
            'activeTab' => 'lost_pets',
        ]);
    }

    #[Route('/user/found-pets', name: 'app_user_found_pets')]
    #[IsGranted('ROLE_USER')]
    public function userFoundPets(LostPetsRepository $lostPetsRepository, FoundAnimalsRepository $foundAnimalsRepository): Response
    {
        $user = $this->getUser();

        // Obtener todos los animales encontrados del usuario con toda la información
        $foundAnimals = $foundAnimalsRepository->findByUserWithRelations($user);

        // Obtener estadísticas
        $stats = $this->getUserStats($lostPetsRepository, $foundAnimalsRepository, $user);

        return $this->render('user/found_pets.html.twig', [
            'user' => $user,
            'foundAnimals' => $foundAnimals,
            'stats' => $stats,
            'activeTab' => 'found_pets',
        ]);
    }

    #[Route('/user/settings', name: 'app_user_settings')]
    #[IsGranted('ROLE_USER')]
    public function userSettings(Request $request, LostPetsRepository $lostPetsRepository, FoundAnimalsRepository $foundAnimalsRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Crear el formulario de edición de perfil
        $form = $this->createForm(UserProfileUpdateType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Actualizar la fecha de modificación
                $user->setUpdatedAt(new \DateTimeImmutable());

                // Persistir los cambios
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', $this->translator->trans('user.settings.messages.profile_updated'));
                return $this->redirectToRoute('app_user_settings');
            } catch (\Exception $e) {
                $this->addFlash('error', $this->translator->trans('user.settings.messages.update_error', ['error' => $e->getMessage()]));
            }
        }

        // Obtener estadísticas
        $stats = $this->getUserStats($lostPetsRepository, $foundAnimalsRepository, $user);

        return $this->render('user/settings.html.twig', [
            'user' => $user,
            'stats' => $stats,
            'activeTab' => 'settings',
            'form' => $form,
        ]);
    }

    /**
     * Helper method to get user statistics
     */
    private function getUserStats($lostPetsRepository = null, $foundAnimalsRepository = null, $user = null): array
    {
        // If we have both repositories and user, use them directly
        if ($lostPetsRepository && $foundAnimalsRepository && $user) {
            $totalLostPets = $lostPetsRepository->countActiveByUser($user);
            $totalFoundAnimals = $foundAnimalsRepository->countActiveByUser($user);
        } else {
            // Fallback for backward compatibility
            $totalLostPets = 0;
            $totalFoundAnimals = 0;
        }

        return [
            'totalLostPets' => $totalLostPets,
            'totalFoundAnimals' => $totalFoundAnimals,
            'totalPublications' => $totalLostPets + $totalFoundAnimals,
        ];
    }
}
