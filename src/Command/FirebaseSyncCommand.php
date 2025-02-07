<?php
namespace App\Command;

use App\Service\FirebaseSyncService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:firebase-sync')]
class FirebaseSyncCommand extends Command
{
    protected static $defaultName = 'app:firebase-sync';
    private FirebaseSyncService $firebaseSyncService;

    public function __construct(FirebaseSyncService $firebaseSyncService)
    {
        parent::__construct();
        $this->firebaseSyncService = $firebaseSyncService;
    }

    protected function configure()
    {
        $this->setDescription('Synchronise les utilisateurs Firebase Auth avec la base locale.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Synchronisation Firebase Auth → Base locale...');
        
        while (true) {
            $this->firebaseSyncService->syncUsers();
            sleep(10); // Pause de 10 secondes
        }

        return Command::SUCCESS;
    }
}
