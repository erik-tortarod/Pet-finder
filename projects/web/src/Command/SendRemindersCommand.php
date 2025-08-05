<?php

namespace App\Command;

use App\Service\ReminderService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
   name: 'app:send-reminders',
   description: 'Send reminders for old animal publications',
)]
class SendRemindersCommand extends Command
{
   public function __construct(
      private ReminderService $reminderService
   ) {
      parent::__construct();
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);

      $io->info('Checking for old publications and sending reminders...');

      try {
         $this->reminderService->checkAndSendReminders();
         $io->success('Reminders sent successfully!');
         return Command::SUCCESS;
      } catch (\Exception $e) {
         $io->error('Error sending reminders: ' . $e->getMessage());
         return Command::FAILURE;
      }
   }
}
