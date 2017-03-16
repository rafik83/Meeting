<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Fix Event's invoice_prefix_id and Invoice's invoice_number fields
 */
class Version20170316110122 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event CHANGE invoice_prefix_id invoice_prefix_id INT NOT NULL');
        $this->addSql('DROP INDEX invoice_number ON invoice');
        $this->addSql('CREATE UNIQUE INDEX invoice_number ON invoice (prefix_id, invoice_prefix, invoice_year, invoice_increment)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event CHANGE invoice_prefix_id invoice_prefix_id INT DEFAULT NULL');
        $this->addSql('DROP INDEX invoice_number ON invoice');
        $this->addSql('CREATE UNIQUE INDEX invoice_number ON invoice (invoice_prefix, invoice_year, invoice_increment)');
    }
}
