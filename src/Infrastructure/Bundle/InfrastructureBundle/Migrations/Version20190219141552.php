<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add availability_type, Drop disable_unavailability_management and migration data
 */
class Version20190219141552 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE type ADD availability_type VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE type SET availability_type = IF(disable_unavailability_management, \'none\', \'unavailable\')');
        //$this->addSql('ALTER TABLE type DROP disable_unavailability_management');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        //$this->addSql('ALTER TABLE type ADD disable_unavailability_management TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('UPDATE type SET disable_unavailability_management = IF(availability_type = \'none\', 1, 0)');
        $this->addSql('ALTER TABLE type DROP availability_type');
    }
}
