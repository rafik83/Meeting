<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add User Agenda Version table
 */
class Version20170623151535 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_agenda_version (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, user_id INT NOT NULL, version LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', created_at DATETIME NOT NULL, INDEX IDX_575D6EDB71F7E88B (event_id), INDEX IDX_575D6EDBA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_agenda_version ADD CONSTRAINT FK_575D6EDB71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_agenda_version ADD CONSTRAINT FK_575D6EDBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user_agenda_version');
    }
}
