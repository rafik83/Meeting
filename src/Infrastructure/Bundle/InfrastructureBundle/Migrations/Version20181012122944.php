<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add mass_unavailability_types table
 */
class Version20181012122944 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE mass_unavailability_types (mass_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_108BB78EEA5DA7EB (mass_id), INDEX IDX_108BB78EC54C8C93 (type_id), PRIMARY KEY(mass_id, type_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mass_unavailability_types ADD CONSTRAINT FK_108BB78EEA5DA7EB FOREIGN KEY (mass_id) REFERENCES mass_unavailability (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mass_unavailability_types ADD CONSTRAINT FK_108BB78EC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');

        $this->addSql('
            INSERT mass_unavailability_types(mass_id, type_id)
                SELECT mass_unavailability.id, type.id
                FROM mass_unavailability
                INNER JOIN event on event.id = mass_unavailability.event_id
                INNER JOIN type on event.id = type.event_id
        ');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE mass_unavailability_types');
    }
}
