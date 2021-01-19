<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170529083856 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tip ADD on_sheet TINYINT(1) DEFAULT \'0\' NOT NULL, ADD on_program TINYINT(1) DEFAULT \'0\' NOT NULL, ADD on_agenda TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE on_meeting_management on_meeting_management TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE on_catalog on_catalog TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE on_print_planning on_print_planning TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tip DROP on_sheet, DROP on_program, DROP on_agenda, CHANGE on_meeting_management on_meeting_management TINYINT(1) NOT NULL, CHANGE on_catalog on_catalog TINYINT(1) NOT NULL, CHANGE on_print_planning on_print_planning TINYINT(1) NOT NULL');
    }
}
