<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add duplicated_from field in Event table
 */
class Version20170731160529 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event ADD duplicated_from_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7F0949296 FOREIGN KEY (duplicated_from_id) REFERENCES event (id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7F0949296 ON event (duplicated_from_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7F0949296');
        $this->addSql('DROP INDEX IDX_3BAE0AA7F0949296 ON event');
        $this->addSql('ALTER TABLE event DROP duplicated_from_id');
    }
}
