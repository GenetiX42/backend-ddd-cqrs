<?php

declare(strict_types=1);

namespace UserInterface\Commands;

use App\Command\CreateFleetCommand;
use App\Handler\CreateFleetHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'fleet:create',
    description: 'Create a new fleet for a user'
)]
final class CreateFleetConsoleCommand extends Command
{
    public function __construct(
        private readonly CreateFleetHandler $createFleetHandler
    ) {
        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->addArgument('userId', InputArgument::REQUIRED, 'User ID')
            ->setHelp('This command creates a new fleet for a user and returns the fleet ID.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = $input->getArgument('userId');

        // Validate user input
        if (!is_string($userId) || trim($userId) === '') {
            $output->writeln('<error>User ID cannot be empty</error>');
            return Command::FAILURE;
        }

        try {
            $command = new \App\Command\CreateFleetCommand($userId);
            $fleetId = $this->createFleetHandler->handle($command);

            $output->writeln($fleetId->toString());

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');

            return Command::FAILURE;
        }
    }
}

