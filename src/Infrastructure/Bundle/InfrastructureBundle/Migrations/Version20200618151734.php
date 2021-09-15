<?php declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add sheet_import_mapping table
 */
final class Version20200618151734 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sheet_import_mapping (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, mapping LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_D3E23C6371F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sheet_import_mapping ADD CONSTRAINT FK_D3E23C6371F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sheet_import_mapping');
    }
}
