<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add Invoice to `order`
 */
class Version20170223135652 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE _order ADD invoice_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE _order ADD CONSTRAINT FK_7F117F042989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('CREATE INDEX IDX_7F117F042989F1FD ON _order (invoice_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE _order DROP FOREIGN KEY FK_7F117F042989F1FD');
        $this->addSql('DROP INDEX IDX_7F117F042989F1FD ON _order');
        $this->addSql('ALTER TABLE _order DROP invoice_id');
    }
}
