<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add table sheet available slot
 */
class Version20170927131333 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sheet_available_slot (sheet_id INT NOT NULL, slot_id INT NOT NULL, INDEX IDX_FA294CD88B1206A5 (sheet_id), INDEX IDX_FA294CD859E5119C (slot_id), PRIMARY KEY(sheet_id, slot_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sheet_available_slot ADD CONSTRAINT FK_FA294CD88B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sheet_available_slot ADD CONSTRAINT FK_FA294CD859E5119C FOREIGN KEY (slot_id) REFERENCES meeting_slot (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sheet_available_slot');
    }
}
