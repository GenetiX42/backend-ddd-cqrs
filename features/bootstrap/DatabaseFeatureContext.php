<?php

declare(strict_types=1);

use App\Command\CreateFleetCommand;
use App\Command\ParkVehicleCommand;
use App\Command\RegisterVehicleCommand;
use App\Handler\CreateFleetHandler;
use App\Handler\ParkVehicleHandler;
use App\Handler\RegisterVehicleHandler;
use App\Handler\GetVehicleLocationHandler;
use App\Query\GetVehicleLocationQuery;
use App\Kernel;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Domain\FleetRepositoryInterface;
use Domain\UserRepositoryInterface;
use Domain\ValueObject\FleetId;
use Domain\ValueObject\Location;
use Domain\ValueObject\UserId;
use Domain\ValueObject\VehicleId;
use Domain\VehicleRepositoryInterface;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Feature context for BDD tests with database persistence.
 */
class DatabaseFeatureContext implements Context
{
    private Kernel $kernel;
    private FleetRepositoryInterface $fleetRepository;
    private VehicleRepositoryInterface $vehicleRepository;
    private UserRepositoryInterface $userRepository;
    private CreateFleetHandler $createFleetHandler;
    private RegisterVehicleHandler $registerVehicleHandler;
    private ParkVehicleHandler $parkVehicleHandler;
    private GetVehicleLocationHandler $getVehicleLocationHandler;
    private EntityManagerInterface $entityManager;

    private ?FleetId $myFleetId = null;
    private ?FleetId $anotherUserFleetId = null;
    private ?VehicleId $vehicleId = null;
    private ?Location $location = null;
    private ?\Exception $lastException = null;

    public function __construct()
    {
        $projectDir = \dirname(__DIR__, 2);
        if (\class_exists(Dotenv::class) && \is_file($projectDir.'/.env')) {
            $dotenv = new Dotenv();
            $dotenv->loadEnv($projectDir.'/.env');
        }

        $this->kernel = new Kernel('test', true);
        $this->kernel->boot();
        $container = $this->kernel->getContainer();

        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->fleetRepository = $container->get(FleetRepositoryInterface::class);
        $this->vehicleRepository = $container->get(VehicleRepositoryInterface::class);
        $this->userRepository = $container->get(UserRepositoryInterface::class);

        $this->createFleetHandler = new CreateFleetHandler($this->fleetRepository, $this->userRepository);
        $this->registerVehicleHandler = new RegisterVehicleHandler(
            $this->fleetRepository,
            $this->vehicleRepository,
            $this->entityManager
        );
        $this->parkVehicleHandler = new ParkVehicleHandler(
            $this->fleetRepository,
            $this->vehicleRepository
        );
        $this->getVehicleLocationHandler = new GetVehicleLocationHandler(
            $this->fleetRepository,
            $this->vehicleRepository
        );
    }

    /**
     * @BeforeScenario
     */
    public function cleanDatabase(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('SET session_replication_role = replica');

        try {
            $connection->executeStatement('DROP TABLE IF EXISTS fleet_vehicles CASCADE');
            $connection->executeStatement('DROP TABLE IF EXISTS fleets CASCADE');
            $connection->executeStatement('DROP TABLE IF EXISTS vehicles CASCADE');
            $connection->executeStatement('DROP TABLE IF EXISTS users CASCADE');

            $schemaTool = new SchemaTool($this->entityManager);
            $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

            if ($metadata === []) {
                throw new \RuntimeException('No metadata found for Doctrine entities.');
            }

            $schemaTool->createSchema($metadata);
            $this->entityManager->clear();
        } finally {
            $connection->executeStatement('SET session_replication_role = DEFAULT');
        }
    }

    /**
     * @Given my fleet
     */
    public function myFleet(): void
    {
        // User will be auto-created by CreateFleetHandler if needed
        $command = new CreateFleetCommand('user-1');
        $this->myFleetId = $this->createFleetHandler->handle($command);
    }

    /**
     * @Given a vehicle
     */
    public function aVehicle(): void
    {
        $this->vehicleId = VehicleId::fromPlateNumber('ABC-123');
    }

    /**
     * @When I register this vehicle into my fleet
     */
    public function iRegisterThisVehicleIntoMyFleet(): void
    {
        $this->lastException = null;
        try {
            $command = new RegisterVehicleCommand(
                $this->myFleetId->toString(),
                $this->vehicleId->getPlateNumber()
            );
            $this->registerVehicleHandler->handle($command);
        } catch (\Exception $e) {
            $this->lastException = $e;
        }
    }

    /**
     * @Then this vehicle should be part of my vehicle fleet
     */
    public function thisVehicleShouldBePartOfMyVehicleFleet(): void
    {
        $fleet = $this->fleetRepository->findById($this->myFleetId);
        if ($fleet === null) {
            throw new \RuntimeException('Fleet not found');
        }
        if (!$fleet->hasVehicle($this->vehicleId)) {
            throw new \RuntimeException('Vehicle is not part of the fleet');
        }
    }

    /**
     * @Given I have registered this vehicle into my fleet
     */
    public function iHaveRegisteredThisVehicleIntoMyFleet(): void
    {
        $command = new RegisterVehicleCommand(
            $this->myFleetId->toString(),
            $this->vehicleId->getPlateNumber()
        );
        $this->registerVehicleHandler->handle($command);
    }

    /**
     * @When I try to register this vehicle into my fleet
     */
    public function iTryToRegisterThisVehicleIntoMyFleet(): void
    {
        $this->lastException = null;
        try {
            $command = new RegisterVehicleCommand(
                $this->myFleetId->toString(),
                $this->vehicleId->getPlateNumber()
            );
            $this->registerVehicleHandler->handle($command);
        } catch (\Exception $e) {
            $this->lastException = $e;
        }
    }

    /**
     * @Then I should be informed this this vehicle has already been registered into my fleet
     */
    public function iShouldBeInformedThisThisVehicleHasAlreadyBeenRegisteredIntoMyFleet(): void
    {
        if ($this->lastException === null) {
            throw new \RuntimeException('Expected an exception but none was thrown');
        }

        if (strpos($this->lastException->getMessage(), 'already been registered') === false) {
            throw new \RuntimeException(
                'Expected exception about vehicle already registered, got: ' . $this->lastException->getMessage()
            );
        }
    }

    /**
     * @Given the fleet of another user
     */
    public function theFleetOfAnotherUser(): void
    {
        // User will be auto-created by CreateFleetHandler if needed
        $command = new CreateFleetCommand('user-2');
        $this->anotherUserFleetId = $this->createFleetHandler->handle($command);
    }

    /**
     * @Given this vehicle has been registered into the other user's fleet
     */
    public function thisVehicleHasBeenRegisteredIntoTheOtherUsersFleet(): void
    {
        $command = new RegisterVehicleCommand(
            $this->anotherUserFleetId->toString(),
            $this->vehicleId->getPlateNumber()
        );
        $this->registerVehicleHandler->handle($command);
    }

    /**
     * @Given a location
     */
    public function aLocation(): void
    {
        $this->location = Location::create(48.8566, 2.3522);
    }

    /**
     * @When I park my vehicle at this location
     */
    public function iParkMyVehicleAtThisLocation(): void
    {
        $this->lastException = null;
        try {
            $command = new ParkVehicleCommand(
                $this->myFleetId->toString(),
                $this->vehicleId->getPlateNumber(),
                (string) $this->location->getLatitude(),
                (string) $this->location->getLongitude(),
                $this->location->getAltitude() !== null ? (string) $this->location->getAltitude() : null
            );
            $this->parkVehicleHandler->handle($command);
        } catch (\Exception $e) {
            $this->lastException = $e;
        }
    }

    /**
     * @Then the known location of my vehicle should verify this location
     */
    public function theKnownLocationOfMyVehicleShouldVerifyThisLocation(): void
    {
        $query = new GetVehicleLocationQuery(
            $this->myFleetId,
            $this->vehicleId
        );
        $location = $this->getVehicleLocationHandler->handle($query);

        if ($location === null) {
            throw new \RuntimeException('Vehicle location is null');
        }

        if (!$location->equals($this->location)) {
            throw new \RuntimeException('Vehicle location does not match expected location');
        }
    }

    /**
     * @Given my vehicle has been parked into this location
     */
    public function myVehicleHasBeenParkedIntoThisLocation(): void
    {
        $command = new ParkVehicleCommand(
            $this->myFleetId->toString(),
            $this->vehicleId->getPlateNumber(),
            (string) $this->location->getLatitude(),
            (string) $this->location->getLongitude(),
            $this->location->getAltitude() !== null ? (string) $this->location->getAltitude() : null
        );
        $this->parkVehicleHandler->handle($command);
    }

    /**
     * @When I try to park my vehicle at this location
     */
    public function iTryToParkMyVehicleAtThisLocation(): void
    {
        $this->lastException = null;
        try {
            $command = new ParkVehicleCommand(
                $this->myFleetId->toString(),
                $this->vehicleId->getPlateNumber(),
                (string) $this->location->getLatitude(),
                (string) $this->location->getLongitude(),
                $this->location->getAltitude() !== null ? (string) $this->location->getAltitude() : null
            );
            $this->parkVehicleHandler->handle($command);
        } catch (\Exception $e) {
            $this->lastException = $e;
        }
    }

    /**
     * @Then I should be informed that my vehicle is already parked at this location
     */
    public function iShouldBeInformedThatMyVehicleIsAlreadyParkedAtThisLocation(): void
    {
        if ($this->lastException === null) {
            throw new \RuntimeException('Expected an exception but none was thrown');
        }

        if (strpos($this->lastException->getMessage(), 'already parked') === false) {
            throw new \RuntimeException(
                'Expected exception about vehicle already parked, got: ' . $this->lastException->getMessage()
            );
        }
    }

    public function __destruct()
    {
        if (isset($this->kernel)) {
            $this->kernel->shutdown();
        }
    }
}

