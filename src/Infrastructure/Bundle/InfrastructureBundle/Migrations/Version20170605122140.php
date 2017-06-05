<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add `event` to table `meeting`
 * Add INDEX `state` to table `meeting`
 */
class Version20170605122140 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE meeting ADD event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT FK_F515E13971F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_F515E13971F7E88B ON meeting (event_id)');
        $this->addSql('CREATE INDEX state ON meeting_request (state)');

        // Add event_id to meeting
        $this->addSql('
            UPDATE `meeting`, `event`, `sheet`
            SET `meeting`.`event_id` = `sheet`.`event_id`
            WHERE `meeting`.`from_sheet_id` = `sheet`.`id` OR `meeting`.`to_sheet_id` = `sheet`.`id`
        ');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE meeting DROP FOREIGN KEY FK_F515E13971F7E88B');
        $this->addSql('DROP INDEX IDX_F515E13971F7E88B ON meeting');
        $this->addSql('ALTER TABLE meeting DROP event_id');
        $this->addSql('DROP INDEX state ON meeting_request');
    }
}
