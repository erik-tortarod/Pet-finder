<?php

namespace App\Command;

use App\Repository\AnimalsRepository;
use App\Repository\LostPetsRepository;
use App\Repository\FoundAnimalsRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

#[AsCommand(
   name: 'app:generate-sitemap',
   description: 'Generate a static sitemap.xml file',
)]
class GenerateSitemapCommand extends Command
{
   public function __construct(
      private RouterInterface $router,
      private AnimalsRepository $animalsRepository,
      private LostPetsRepository $lostPetsRepository,
      private FoundAnimalsRepository $foundAnimalsRepository
   ) {
      parent::__construct();
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);

      $urls = [];

      // Add static pages
      $urls[] = [
         'loc' => $this->router->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
         'lastmod' => date('Y-m-d'),
         'changefreq' => 'daily',
         'priority' => '1.0'
      ];

      $urls[] = [
         'loc' => $this->router->generate('app_lost_pets', [], UrlGeneratorInterface::ABSOLUTE_URL),
         'lastmod' => date('Y-m-d'),
         'changefreq' => 'hourly',
         'priority' => '0.9'
      ];

      $urls[] = [
         'loc' => $this->router->generate('app_found_pets', [], UrlGeneratorInterface::ABSOLUTE_URL),
         'lastmod' => date('Y-m-d'),
         'changefreq' => 'hourly',
         'priority' => '0.9'
      ];

      // Add create pages (important for SEO)
      $urls[] = [
         'loc' => $this->router->generate('app_lost_pets_create', [], UrlGeneratorInterface::ABSOLUTE_URL),
         'lastmod' => date('Y-m-d'),
         'changefreq' => 'monthly',
         'priority' => '0.6'
      ];

      $urls[] = [
         'loc' => $this->router->generate('app_found_pets_create', [], UrlGeneratorInterface::ABSOLUTE_URL),
         'lastmod' => date('Y-m-d'),
         'changefreq' => 'monthly',
         'priority' => '0.6'
      ];

      // Add general animal page
      $urls[] = [
         'loc' => $this->router->generate('app_animal', [], UrlGeneratorInterface::ABSOLUTE_URL),
         'lastmod' => date('Y-m-d'),
         'changefreq' => 'daily',
         'priority' => '0.8'
      ];

      // Add authentication pages
      $urls[] = [
         'loc' => $this->router->generate('app_auth_login', [], UrlGeneratorInterface::ABSOLUTE_URL),
         'lastmod' => date('Y-m-d'),
         'changefreq' => 'monthly',
         'priority' => '0.3'
      ];

      $urls[] = [
         'loc' => $this->router->generate('app_auth_register', [], UrlGeneratorInterface::ABSOLUTE_URL),
         'lastmod' => date('Y-m-d'),
         'changefreq' => 'monthly',
         'priority' => '0.3'
      ];

      $urls[] = [
         'loc' => $this->router->generate('app_auth_forgot_password', [], UrlGeneratorInterface::ABSOLUTE_URL),
         'lastmod' => date('Y-m-d'),
         'changefreq' => 'monthly',
         'priority' => '0.2'
      ];

      // Add shelter verification pages
      $urls[] = [
         'loc' => $this->router->generate('app_shelter_pending_verification', [], UrlGeneratorInterface::ABSOLUTE_URL),
         'lastmod' => date('Y-m-d'),
         'changefreq' => 'monthly',
         'priority' => '0.2'
      ];

      $urls[] = [
         'loc' => $this->router->generate('app_shelter_rejected', [], UrlGeneratorInterface::ABSOLUTE_URL),
         'lastmod' => date('Y-m-d'),
         'changefreq' => 'monthly',
         'priority' => '0.2'
      ];

      // Add language selector pages
      $urls[] = [
         'loc' => $this->router->generate('app_change_locale', ['locale' => 'es'], UrlGeneratorInterface::ABSOLUTE_URL),
         'lastmod' => date('Y-m-d'),
         'changefreq' => 'monthly',
         'priority' => '0.1'
      ];

      $urls[] = [
         'loc' => $this->router->generate('app_change_locale', ['locale' => 'en'], UrlGeneratorInterface::ABSOLUTE_URL),
         'lastmod' => date('Y-m-d'),
         'changefreq' => 'monthly',
         'priority' => '0.1'
      ];

      // Add individual animal pages (lost pets)
      $lostPets = $this->lostPetsRepository->findAll();
      foreach ($lostPets as $lostPet) {
         $animal = $lostPet->getAnimalId();
         if ($animal) {
            $urls[] = [
               'loc' => $this->router->generate('app_animal_show', ['id' => $animal->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
               'lastmod' => $lostPet->getCreatedAt() ? $lostPet->getCreatedAt()->format('Y-m-d') : date('Y-m-d'),
               'changefreq' => 'weekly',
               'priority' => '0.7'
            ];
         }
      }

      // Add individual animal pages (found pets)
      $foundAnimals = $this->foundAnimalsRepository->findAll();
      foreach ($foundAnimals as $foundAnimal) {
         $animal = $foundAnimal->getAnimalId();
         if ($animal) {
            $urls[] = [
               'loc' => $this->router->generate('app_animal_show', ['id' => $animal->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
               'lastmod' => $foundAnimal->getCreatedAt() ? $foundAnimal->getCreatedAt()->format('Y-m-d') : date('Y-m-d'),
               'changefreq' => 'weekly',
               'priority' => '0.7'
            ];
         }
      }

      // Generate XML content
      $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
      $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

      foreach ($urls as $url) {
         $xml .= '    <url>' . "\n";
         $xml .= '        <loc>' . htmlspecialchars($url['loc']) . '</loc>' . "\n";
         $xml .= '        <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
         $xml .= '        <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
         $xml .= '        <priority>' . $url['priority'] . '</priority>' . "\n";
         $xml .= '    </url>' . "\n";
      }

      $xml .= '</urlset>';

      // Write to file
      $sitemapPath = dirname(__DIR__, 2) . '/public/sitemap.xml';
      file_put_contents($sitemapPath, $xml);

      $io->success(sprintf('Sitemap generated successfully with %d URLs at: %s', count($urls), $sitemapPath));

      return Command::SUCCESS;
   }
}
