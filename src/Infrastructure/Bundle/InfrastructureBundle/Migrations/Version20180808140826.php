<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add header button colors on event configuration
 */
class Version20180808140826 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event ADD configuration_header_button_left_color VARCHAR(255) DEFAULT \'#2F2F2F\' NOT NULL, ADD configuration_header_button_right_color VARCHAR(255) DEFAULT \'#2F2F2F\' NOT NULL, ADD configuration_header_button_text_color VARCHAR(255) DEFAULT \'#FFFFFF\' NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event DROP configuration_header_button_left_color, DROP configuration_header_button_right_color, DROP configuration_header_button_text_color');
    }
}
