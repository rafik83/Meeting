<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create table `invoice`
 */
class Version20170222174057 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, sheet_id INT DEFAULT NULL, prefix_id INT DEFAULT NULL, total INT NOT NULL, total_with_vat INT NOT NULL, vat_amount INT NOT NULL, invoice_prefix VARCHAR(255) NOT NULL, invoice_year INT NOT NULL, invoice_increment INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_9065174471F7E88B (event_id), INDEX IDX_906517448B1206A5 (sheet_id), INDEX IDX_906517445C554FFE (prefix_id), UNIQUE INDEX invoice_number (prefix_id, invoice_prefix, invoice_year, invoice_increment), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_9065174471F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517448B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517445C554FFE FOREIGN KEY (prefix_id) REFERENCES invoice_prefix (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DROP TABLE invoice');
    }
}
