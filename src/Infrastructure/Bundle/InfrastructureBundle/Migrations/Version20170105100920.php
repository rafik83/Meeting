<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Messaging/Campaign has one Messaging/Message relationship
 */
class Version20170105100920 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE messaging_campaign ADD message_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE messaging_campaign ADD CONSTRAINT FK_2453C1AE537A1329 FOREIGN KEY (message_id) REFERENCES messaging_message (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_2453C1AE537A1329 ON messaging_campaign (message_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE messaging_campaign DROP FOREIGN KEY FK_2453C1AE537A1329');
        $this->addSql('DROP INDEX IDX_2453C1AE537A1329 ON messaging_campaign');
        $this->addSql('ALTER TABLE messaging_campaign DROP message_id');
    }
}
