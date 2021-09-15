<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add duplicatedFrom on Sheet Group
 */
class Version20180529095236 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sheet_group ADD duplicated_from_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sheet_group ADD CONSTRAINT FK_E95566CF0949296 FOREIGN KEY (duplicated_from_id) REFERENCES sheet_group (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E95566CF0949296 ON sheet_group (duplicated_from_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sheet_group DROP FOREIGN KEY FK_E95566CF0949296');
        $this->addSql('DROP INDEX UNIQ_E95566CF0949296 ON sheet_group');
        $this->addSql('ALTER TABLE sheet_group DROP duplicated_from_id');
    }
}
