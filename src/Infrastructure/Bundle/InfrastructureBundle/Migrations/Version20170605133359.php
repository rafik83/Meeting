<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add field `event_id` on table `meeting_request`
 */
class Version20170605133359 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE meeting_request ADD event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE meeting_request ADD CONSTRAINT FK_A345C71271F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_A345C71271F7E88B ON meeting_request (event_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE meeting_request DROP FOREIGN KEY FK_A345C71271F7E88B');
        $this->addSql('DROP INDEX IDX_A345C71271F7E88B ON meeting_request');
        $this->addSql('ALTER TABLE meeting_request DROP event_id');
    }
}
