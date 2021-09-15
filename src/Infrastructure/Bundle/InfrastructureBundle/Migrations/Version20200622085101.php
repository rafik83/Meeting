<?php declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add mapping and ImportMapping on ParticipantImport.
 */
final class Version20200622085101 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE participant_import ADD import_mapping_id INT DEFAULT NULL, ADD mapping LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE participant_import ADD CONSTRAINT FK_2F32709C383149DF FOREIGN KEY (import_mapping_id) REFERENCES sheet_import_mapping (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_2F32709C383149DF ON participant_import (import_mapping_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE participant_import DROP FOREIGN KEY FK_2F32709C383149DF');
        $this->addSql('DROP INDEX IDX_2F32709C383149DF ON participant_import');
        $this->addSql('ALTER TABLE participant_import DROP import_mapping_id, DROP mapping');
    }
}
