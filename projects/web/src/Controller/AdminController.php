<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

/**
 * @method User getUser()
 */
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

    #[Route('/admin/users', name: 'app_admin_users')]
    #[IsGranted('ROLE_ADMIN')]
    public function users(Request $request, UserRepository $userRepository): Response
    {
        $filter = $request->query->get('filter', 'all');
        $search = $request->query->get('search', '');

        $users = $userRepository->findAll();

        // Aplicar filtros
        if ($filter !== 'all') {
            $users = array_filter($users, function ($user) use ($filter) {
                if ($filter === 'admin') {
                    return in_array('ROLE_ADMIN', $user->getRoles());
                } elseif ($filter === 'shelter') {
                    return $user->isShelter();
                } elseif ($filter === 'regular') {
                    return !in_array('ROLE_ADMIN', $user->getRoles()) && !$user->isShelter();
                }
                return true;
            });
        }

        // Aplicar búsqueda
        if (!empty($search)) {
            $users = array_filter($users, function ($user) use ($search) {
                $searchLower = strtolower($search);
                return str_contains(strtolower($user->getEmail()), $searchLower) ||
                    str_contains(strtolower($user->getFirstName()), $searchLower) ||
                    str_contains(strtolower($user->getLastName()), $searchLower) ||
                    ($user->getShelterName() && str_contains(strtolower($user->getShelterName()), $searchLower));
            });
        }

        // Ordenar por fecha de creación (más recientes primero)
        usort($users, function ($a, $b) {
            return $b->getCreatedAt() <=> $a->getCreatedAt();
        });

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'currentFilter' => $filter,
            'search' => $search,
        ]);
    }

    #[Route('/admin/shelters/{id}/approve', name: 'app_admin_shelter_approve', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function approveShelter(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $shelter = $userRepository->find($id);

        if (!$shelter || !$shelter->isShelter()) {
            $this->addFlash('error', 'Shelter no encontrada');
            return $this->redirectToRoute('app_admin_shelters');
        }

        if ($shelter->getShelterVerificationStatus() !== 'pending') {
            $this->addFlash('error', 'Solo se pueden aprobar shelters pendientes');
            return $this->redirectToRoute('app_admin_shelters');
        }

        try {
            $shelter->setShelterVerificationStatus('VERIFIED');
            $shelter->setShelterVerificationDate(new \DateTime());
            $shelter->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($shelter);
            $entityManager->flush();

            $this->addFlash('success', "Shelter '{$shelter->getShelterName()}' aprobada exitosamente");
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al aprobar la shelter: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_shelters');
    }

    #[Route('/admin/shelters/{id}/reject', name: 'app_admin_shelter_reject', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function rejectShelter(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $shelter = $userRepository->find($id);

        if (!$shelter || !$shelter->isShelter()) {
            $this->addFlash('error', 'Shelter no encontrada');
            return $this->redirectToRoute('app_admin_shelters');
        }

        if ($shelter->getShelterVerificationStatus() !== 'pending') {
            $this->addFlash('error', 'Solo se pueden rechazar shelters pendientes');
            return $this->redirectToRoute('app_admin_shelters');
        }

        try {
            $shelter->setShelterVerificationStatus('rejected');
            $shelter->setShelterVerificationDate(new \DateTime());
            $shelter->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($shelter);
            $entityManager->flush();

            $this->addFlash('success', "Shelter '{$shelter->getShelterName()}' rechazada exitosamente");
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al rechazar la shelter: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_shelters');
    }

    #[Route('/admin/shelters/{id}/change-status', name: 'app_admin_shelter_change_status', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function changeShelterStatus(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $shelter = $userRepository->find($id);

        if (!$shelter || !$shelter->isShelter()) {
            $this->addFlash('error', 'Shelter no encontrada');
            return $this->redirectToRoute('app_admin_shelters');
        }

        $newStatus = $request->request->get('new_status');
        $currentStatus = $shelter->getShelterVerificationStatus();

        // Validar que el nuevo estado sea válido
        $validStatuses = ['pending', 'VERIFIED', 'rejected'];
        if (!in_array($newStatus, $validStatuses)) {
            $this->addFlash('error', 'Estado no válido');
            return $this->redirectToRoute('app_admin_shelters');
        }

        // No permitir cambiar al mismo estado
        if ($newStatus === $currentStatus) {
            $this->addFlash('error', 'El shelter ya tiene ese estado');
            return $this->redirectToRoute('app_admin_shelters');
        }

        try {
            $shelter->setShelterVerificationStatus($newStatus);
            $shelter->setShelterVerificationDate(new \DateTime());
            $shelter->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($shelter);
            $entityManager->flush();

            $statusMessages = [
                'pending' => 'marcada como pendiente',
                'VERIFIED' => 'aprobada',
                'rejected' => 'rechazada'
            ];

            $message = $statusMessages[$newStatus] ?? 'cambiada de estado';
            $this->addFlash('success', "Shelter '{$shelter->getShelterName()}' {$message} exitosamente");
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al cambiar el estado de la shelter: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_shelters');
    }

    #[Route('/admin/users/{id}/toggle-admin', name: 'app_admin_toggle_admin', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function toggleAdminRole(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($id);
        $currentUser = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Usuario no encontrado');
            return $this->redirectToRoute('app_admin_users');
        }

        // No permitir que un admin se quite a sí mismo el rol
        if ($currentUser && $user->getId() === $currentUser->getId()) {
            $this->addFlash('error', 'No puedes modificar tu propio rol de administrador');
            return $this->redirectToRoute('app_admin_users');
        }

        try {
            $roles = $user->getRoles();
            $hasAdminRole = in_array('ROLE_ADMIN', $roles);

            if ($hasAdminRole) {
                // Quitar rol de admin
                $roles = array_filter($roles, function ($role) {
                    return $role !== 'ROLE_ADMIN';
                });
                $message = "Rol de administrador removido de '{$user->getEmail()}' exitosamente";
            } else {
                // Agregar rol de admin
                $roles[] = 'ROLE_ADMIN';
                $message = "Rol de administrador asignado a '{$user->getEmail()}' exitosamente";
            }

            $user->setRoles($roles);
            $user->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', $message);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al modificar el rol del usuario: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_users');
    }
}
