<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Exception thrown when a vehicle does not belong to the specified fleet.
 * This is a security-related exception (400 bad parameters) - we don't provide detailed information.
 */
final class InvalidVehicleFleetAssociationException extends \InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct('Invalid arguments: the provided fleet ID or vehicle plate number is incorrect, or the vehicle is not associated with the specified fleet.');
    }
}

