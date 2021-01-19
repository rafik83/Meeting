<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add Logo and Mobile Logo on Event Translation and Header color on Event
 */
class Version20180615132903 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event ADD configuration_header_left_color VARCHAR(255) DEFAULT NULL, ADD configuration_header_right_color VARCHAR(255) DEFAULT NULL, DROP logo, DROP logo_extension');
        $this->addSql('UPDATE event SET configuration_header_left_color = configuration_left_color');
        $this->addSql('UPDATE event SET configuration_header_right_color = configuration_right_color');
        $this->addSql('ALTER TABLE event CHANGE configuration_header_left_color configuration_header_left_color VARCHAR(255) NOT NULL, CHANGE configuration_header_right_color configuration_header_right_color VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE event_translation ADD logo VARCHAR(255) DEFAULT NULL, ADD logo_extension VARCHAR(255) DEFAULT NULL, ADD mobile_logo VARCHAR(255) DEFAULT NULL, ADD mobile_logo_extension VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event ADD logo VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD logo_extension VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, DROP configuration_header_left_color, DROP configuration_header_right_color');
        $this->addSql('ALTER TABLE event_translation DROP logo, DROP logo_extension, DROP mobile_logo, DROP mobile_logo_extension');
    }
}
