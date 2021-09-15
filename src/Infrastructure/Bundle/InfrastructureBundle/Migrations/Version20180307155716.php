<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add event_extra_data
 */
class Version20180307155716 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE event_extra_data (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, name VARCHAR(255) NOT NULL, value LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_6160E95271F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event_extra_data ADD CONSTRAINT FK_6160E95271F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE event_extra_data');
    }
}
