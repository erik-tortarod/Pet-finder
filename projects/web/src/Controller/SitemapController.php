<?php

namespace App\Controller;

use App\Repository\AnimalsRepository;
use App\Repository\LostPetsRepository;
use App\Repository\FoundAnimalsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'app_sitemap', defaults: ['_format' => 'xml'])]
    public function index(
        AnimalsRepository $animalsRepository,
        LostPetsRepository $lostPetsRepository,
        FoundAnimalsRepository $foundAnimalsRepository
    ): Response {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/xml; charset=utf-8');

        $urls = [];

        // Add static pages
        $urls[] = [
            'loc' => $this->generateUrl('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'daily',
            'priority' => '1.0'
        ];

        $urls[] = [
            'loc' => $this->generateUrl('app_lost_pets', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'hourly',
            'priority' => '0.9'
        ];

        $urls[] = [
            'loc' => $this->generateUrl('app_found_pets', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'hourly',
            'priority' => '0.9'
        ];

        // Add create pages (important for SEO)
        $urls[] = [
            'loc' => $this->generateUrl('app_lost_pets_create', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'monthly',
            'priority' => '0.6'
        ];

        $urls[] = [
            'loc' => $this->generateUrl('app_found_pets_create', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'monthly',
            'priority' => '0.6'
        ];

        // Add general animal page
        $urls[] = [
            'loc' => $this->generateUrl('app_animal', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'daily',
            'priority' => '0.8'
        ];

        // Add authentication pages
        $urls[] = [
            'loc' => $this->generateUrl('app_auth_login', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'monthly',
            'priority' => '0.3'
        ];

        $urls[] = [
            'loc' => $this->generateUrl('app_auth_register', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'monthly',
            'priority' => '0.3'
        ];

        $urls[] = [
            'loc' => $this->generateUrl('app_auth_forgot_password', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'monthly',
            'priority' => '0.2'
        ];

        // Add shelter verification pages
        $urls[] = [
            'loc' => $this->generateUrl('app_shelter_pending_verification', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'monthly',
            'priority' => '0.2'
        ];

        $urls[] = [
            'loc' => $this->generateUrl('app_shelter_rejected', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'monthly',
            'priority' => '0.2'
        ];

        // Add language selector pages
        $urls[] = [
            'loc' => $this->generateUrl('app_change_locale', ['locale' => 'es'], UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'monthly',
            'priority' => '0.1'
        ];

        $urls[] = [
            'loc' => $this->generateUrl('app_change_locale', ['locale' => 'en'], UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'monthly',
            'priority' => '0.1'
        ];

        // Add individual animal pages (lost pets)
        $lostPets = $lostPetsRepository->findAll();
        foreach ($lostPets as $lostPet) {
            $animal = $lostPet->getAnimalId();
            if ($animal) {
                $urls[] = [
                    'loc' => $this->generateUrl('app_animal_show', ['id' => $animal->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    'lastmod' => $lostPet->getCreatedAt() ? $lostPet->getCreatedAt()->format('Y-m-d') : date('Y-m-d'),
                    'changefreq' => 'weekly',
                    'priority' => '0.7'
                ];
            }
        }

        // Add individual animal pages (found pets)
        $foundAnimals = $foundAnimalsRepository->findAll();
        foreach ($foundAnimals as $foundAnimal) {
            $animal = $foundAnimal->getAnimalId();
            if ($animal) {
                $urls[] = [
                    'loc' => $this->generateUrl('app_animal_show', ['id' => $animal->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    'lastmod' => $foundAnimal->getCreatedAt() ? $foundAnimal->getCreatedAt()->format('Y-m-d') : date('Y-m-d'),
                    'changefreq' => 'weekly',
                    'priority' => '0.7'
                ];
            }
        }

        $content = $this->renderView('sitemap/index.xml.twig', [
            'urls' => $urls
        ]);

        $response->setContent($content);

        return $response;
    }
}
