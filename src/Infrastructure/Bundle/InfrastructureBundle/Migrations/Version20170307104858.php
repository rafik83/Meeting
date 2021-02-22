<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Fix invoice_prefix_id field in event table
 */
class Version20170307104858 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA739FABE62');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA739FABE62 FOREIGN KEY (invoice_prefix_id) REFERENCES invoice_prefix (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA739FABE62');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA739FABE62 FOREIGN KEY (invoice_prefix_id) REFERENCES invoice_prefix (id) ON DELETE CASCADE');
    }
}
