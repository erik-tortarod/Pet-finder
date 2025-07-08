<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface;

final class MailerController extends AbstractController
{
    #[Route('/send_email', name: 'send_email', methods: ['POST'])]
    public function index(MailerInterface $mailer, LoggerInterface $logger): JsonResponse
    {
        $logger->info('Starting email send process');

        try {
            $logger->info('Creating email object');

            $email = (new Email())
                ->from('tortarod@gmail.com')
                ->to('eriktortarod@gmail.com')
                ->subject('TEST EMAIL FROM PET FINDER APP - ' . date('Y-m-d H:i:s'))
                ->text('This is a test email from your Pet Finder application!')
                ->html('<h1>Test Email from Pet Finder</h1><p>This email was sent at: ' . date('Y-m-d H:i:s') . '</p><p>If you see this, your mailer is working perfectly!</p>');

            $logger->info('Email object created, attempting to send');

            $mailer->send($email);

            $logger->info('Email sent successfully');

            return new JsonResponse(['message' => 'Email sent successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            $logger->error('Email send failed: ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
