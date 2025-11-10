<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251107180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users, fleets, vehicles and fleet_vehicles tables';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE users (id VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_users_id ON users (id)');
        
        $this->addSql('CREATE TABLE fleets (id UUID NOT NULL, user_id VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_fleets_id ON fleets (id)');
        $this->addSql('CREATE INDEX idx_fleets_user_id ON fleets (user_id)');
        $this->addSql('ALTER TABLE fleets ADD CONSTRAINT FK_FLEETS_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        
        $this->addSql('CREATE TABLE vehicles (plate_number VARCHAR(255) NOT NULL, parked_latitude NUMERIC(10, 7) DEFAULT NULL, parked_longitude NUMERIC(10, 7) DEFAULT NULL, parked_altitude NUMERIC(10, 2) DEFAULT NULL, parked_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(plate_number))');
        $this->addSql('CREATE INDEX idx_vehicles_plate_number ON vehicles (plate_number)');
        
        $this->addSql('CREATE TABLE fleet_vehicles (fleet_id UUID NOT NULL, vehicle_plate_number VARCHAR(255) NOT NULL, PRIMARY KEY(fleet_id, vehicle_plate_number))');
        $this->addSql('CREATE INDEX idx_fleet_vehicles_fleet_id ON fleet_vehicles (fleet_id)');
        $this->addSql('CREATE INDEX idx_fleet_vehicles_vehicle_plate_number ON fleet_vehicles (vehicle_plate_number)');
        $this->addSql('ALTER TABLE fleet_vehicles ADD CONSTRAINT FK_FLEET_VEHICLES_FLEET FOREIGN KEY (fleet_id) REFERENCES fleets (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fleet_vehicles ADD CONSTRAINT FK_FLEET_VEHICLES_VEHICLE FOREIGN KEY (vehicle_plate_number) REFERENCES vehicles (plate_number) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fleet_vehicles DROP CONSTRAINT FK_FLEET_VEHICLES_FLEET');
        $this->addSql('ALTER TABLE fleet_vehicles DROP CONSTRAINT FK_FLEET_VEHICLES_VEHICLE');
        $this->addSql('ALTER TABLE fleets DROP CONSTRAINT FK_FLEETS_USER');
        $this->addSql('DROP TABLE fleet_vehicles');
        $this->addSql('DROP TABLE vehicles');
        $this->addSql('DROP TABLE fleets');
        $this->addSql('DROP TABLE users');
    }
}

