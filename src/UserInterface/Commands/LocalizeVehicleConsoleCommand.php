<?php

declare(strict_types=1);

namespace UserInterface\Commands;

use App\Command\ParkVehicleCommand;
use App\Handler\ParkVehicleHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'fleet:localize-vehicle',
    description: 'Localize a vehicle at a specific location'
)]
final class LocalizeVehicleConsoleCommand extends Command
{
    public function __construct(
        private readonly ParkVehicleHandler $parkVehicleHandler
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('fleetId', InputArgument::REQUIRED, 'Fleet ID')
            ->addArgument('vehiclePlateNumber', InputArgument::REQUIRED, 'Vehicle plate number')
            ->addArgument('lat', InputArgument::REQUIRED, 'Latitude')
            ->addArgument('lng', InputArgument::REQUIRED, 'Longitude')
            ->addArgument('alt', InputArgument::OPTIONAL, 'Altitude')
            ->setHelp('This command localizes a vehicle at a specific GPS location.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fleetIdString = $input->getArgument('fleetId');
        $vehiclePlateNumber = $input->getArgument('vehiclePlateNumber');
        $latString = $input->getArgument('lat');
        $lngString = $input->getArgument('lng');
        $altString = $input->getArgument('alt');

        // Basic validation: only check that required arguments are provided and non-empty
        if (!is_string($fleetIdString) || trim($fleetIdString) === '') {
            $output->writeln('<error>Fleet ID cannot be empty</error>');
            return Command::FAILURE;
        }

        if (!is_string($vehiclePlateNumber) || trim($vehiclePlateNumber) === '') {
            $output->writeln('<error>Vehicle plate number cannot be empty</error>');
            return Command::FAILURE;
        }

        if (!is_string($latString) || trim($latString) === '') {
            $output->writeln('<error>Latitude cannot be empty</error>');
            return Command::FAILURE;
        }

        if (!is_string($lngString) || trim($lngString) === '') {
            $output->writeln('<error>Longitude cannot be empty</error>');
            return Command::FAILURE;
        }

        try {
            // The command will create Value Objects which will validate the input
            $command = new ParkVehicleCommand($fleetIdString, $vehiclePlateNumber, $latString, $lngString, $altString);
            $this->parkVehicleHandler->handle($command);

            $output->writeln('<info>Vehicle localized successfully</info>');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');

            return Command::FAILURE;
        }
    }
}

