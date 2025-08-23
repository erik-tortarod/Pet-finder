<?php

namespace App\Command;

use App\Service\TelegramService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
   name: 'app:test-telegram',
   description: 'Envía un mensaje de prueba a Telegram',
)]
class TestTelegramCommand extends Command
{
   public function __construct(
      private TelegramService $telegramService
   ) {
      parent::__construct();
   }

   protected function configure(): void
   {
      $this
         ->addOption(
            'message',
            'm',
            InputOption::VALUE_REQUIRED,
            'Mensaje a enviar',
            '🧪 Mensaje de prueba desde Pet Finder'
         )
         ->setHelp('Este comando envía un mensaje de prueba a Telegram. Ejemplos:
                <info>php bin/console app:test-telegram</info> - Envía mensaje por defecto
                <info>php bin/console app:test-telegram --message="Hola desde Pet Finder"</info> - Envía mensaje personalizado');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $message = $input->getOption('message');

      $io->title('📱 Test de Telegram - Pet Finder');
      $io->text("Enviando mensaje: <info>{$message}</info>");

      try {
         $success = $this->telegramService->sendMessage($message);

         if ($success) {
            $io->success('✅ Mensaje enviado correctamente a Telegram!');
            $io->text('Revisa tu chat de Telegram para ver el mensaje.');
         } else {
            $io->error('❌ Error al enviar mensaje a Telegram');
            $io->text('Verifica que las variables TELEGRAM_BOT_TOKEN y TELEGRAM_CHAT_ID estén configuradas correctamente en tu .env.local');
         }

         return $success ? Command::SUCCESS : Command::FAILURE;
      } catch (\Exception $e) {
         $io->error('Error al enviar mensaje: ' . $e->getMessage());
         return Command::FAILURE;
      }
   }
}
