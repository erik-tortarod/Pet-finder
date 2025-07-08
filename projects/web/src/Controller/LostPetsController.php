<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LostPetsController extends AbstractController
{
    #[Route('/lost/pets', name: 'app_lost_pets')]
    public function index(): Response
    {
        return $this->render('lost_pets/index.html.twig', [
            'controller_name' => 'LostPetsController',
        ]);
    }
}
