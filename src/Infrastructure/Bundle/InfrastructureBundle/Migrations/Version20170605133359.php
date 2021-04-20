<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add field `event_id` on table `meeting_request`
 */
class Version20170605133359 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE meeting_request ADD event_id INT DEFAULT NULL');

        // Add event_id to meeting_request
        $this->addSql('
            UPDATE `meeting_request`, `sheet`
            SET `meeting_request`.`event_id` = `sheet`.`event_id`
            WHERE `meeting_request`.`from_id` = `sheet`.`id`
        ');

        $this->addSql('CREATE INDEX IDX_A345C71271F7E88B ON meeting_request (event_id)');

        $this->addSql('ALTER TABLE meeting_request CHANGE event_id event_id INT NOT NULL');
        $this->addSql('ALTER TABLE meeting_request ADD CONSTRAINT FK_A345C71271F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE meeting_request DROP FOREIGN KEY FK_A345C71271F7E88B');
        $this->addSql('DROP INDEX IDX_A345C71271F7E88B ON meeting_request');
        $this->addSql('ALTER TABLE meeting_request DROP event_id');
    }
}
