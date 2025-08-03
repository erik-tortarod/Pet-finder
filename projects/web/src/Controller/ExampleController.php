<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExampleController extends AbstractController
{
   #[Route('/examples/confirm-modal', name: 'app_examples_confirm_modal')]
   public function confirmModalExample(): Response
   {
      return $this->render('examples/confirm_modal_example.html.twig');
   }
}
