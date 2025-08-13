<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TelegramService
{
   private string $botToken;
   private string $chatId;
   private string $apiUrl = 'https://api.telegram.org/bot';

   public function __construct(
      private HttpClientInterface $httpClient,
      ParameterBagInterface $parameterBag
   ) {
      $this->botToken = $parameterBag->get('app.telegram.bot_token');
      $this->chatId = $parameterBag->get('app.telegram.chat_id');
   }

   public function sendMessage(string $message): bool
   {
      try {
         $response = $this->httpClient->request('POST', $this->apiUrl . $this->botToken . '/sendMessage', [
            'json' => [
               'chat_id' => $this->chatId,
               'text' => $message,
               'parse_mode' => 'HTML'
            ]
         ]);

         return $response->getStatusCode() === 200;
      } catch (\Exception $e) {
         // Log error if needed
         return false;
      }
   }

   public function sendShelterRegistrationNotification(array $shelterData): bool
   {
      $message = $this->formatShelterRegistrationMessage($shelterData);
      return $this->sendMessage($message);
   }

   private function formatShelterRegistrationMessage(array $shelterData): string
   {
      $message = "🏠 <b>Nueva Protectora Registrada</b>\n\n";
      $message .= "📝 <b>Nombre:</b> " . htmlspecialchars($shelterData['name']) . "\n";
      $message .= "📧 <b>Email:</b> " . htmlspecialchars($shelterData['email']) . "\n";
      $message .= "📞 <b>Teléfono:</b> " . htmlspecialchars($shelterData['phone'] ?? 'No especificado') . "\n";
      $message .= "📍 <b>Dirección:</b> " . htmlspecialchars($shelterData['address'] ?? 'No especificada') . "\n";

      if (!empty($shelterData['description'])) {
         $message .= "📋 <b>Descripción:</b> " . htmlspecialchars($shelterData['description']) . "\n";
      }

      if (!empty($shelterData['website'])) {
         $message .= "🌐 <b>Sitio web:</b> " . htmlspecialchars($shelterData['website']) . "\n";
      }

      if (!empty($shelterData['facebook'])) {
         $message .= "📘 <b>Facebook:</b> " . htmlspecialchars($shelterData['facebook']) . "\n";
      }

      $message .= "\n⏰ <b>Fecha de registro:</b> " . $shelterData['created_at']->format('d/m/Y H:i:s') . "\n";
      $message .= "🆔 <b>ID de usuario:</b> " . $shelterData['id'];

      return $message;
   }
}
