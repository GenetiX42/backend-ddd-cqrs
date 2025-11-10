<?php

declare(strict_types=1);

namespace UserInterface\Commands;

use App\Command\RegisterVehicleCommand;
use App\Handler\RegisterVehicleHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'fleet:register-vehicle',
    description: 'Register a vehicle in a fleet'
)]
final class RegisterVehicleConsoleCommand extends Command
{
    public function __construct(
        private readonly RegisterVehicleHandler $registerVehicleHandler
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('fleetId', InputArgument::REQUIRED, 'Fleet ID')
            ->addArgument('vehiclePlateNumber', InputArgument::REQUIRED, 'Vehicle plate number')
            ->setHelp('This command registers a vehicle in a fleet.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fleetIdString = $input->getArgument('fleetId');
        $vehiclePlateNumber = $input->getArgument('vehiclePlateNumber');

        // Basic validation: only check that arguments are provided and non-empty
        if (!is_string($fleetIdString) || trim($fleetIdString) === '') {
            $output->writeln('<error>Fleet ID cannot be empty</error>');
            return Command::FAILURE;
        }

        if (!is_string($vehiclePlateNumber) || trim($vehiclePlateNumber) === '') {
            $output->writeln('<error>Vehicle plate number cannot be empty</error>');
            return Command::FAILURE;
        }

        try {
            // The command will create Value Objects which will validate the input
            $command = new RegisterVehicleCommand($fleetIdString, $vehiclePlateNumber);
            $vehicleId = $this->registerVehicleHandler->handle($command);

            $output->writeln('<info>Vehicle registered successfully</info>');
            $output->writeln($vehicleId->getPlateNumber());

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');

            return Command::FAILURE;
        }
    }
}

