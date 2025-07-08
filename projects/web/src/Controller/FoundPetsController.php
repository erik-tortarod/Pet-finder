<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FoundPetsController extends AbstractController
{
    #[Route('/found/pets', name: 'app_found_pets')]
    public function index(): Response
    {
        return $this->render('found_pets/index.html.twig', [
            'controller_name' => 'FoundPetsController',
        ]);
    }
}
