<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Fix uniqueness constraint group (event_id, duplicated_from_id)
 */
class Version20190322105040 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sheet_group DROP INDEX UNIQ_E95566CF0949296, ADD INDEX IDX_E95566CF0949296 (duplicated_from_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_duplicated_group_event ON sheet_group (event_id, duplicated_from_id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sheet_group DROP INDEX IDX_E95566CF0949296, ADD UNIQUE INDEX UNIQ_E95566CF0949296 (duplicated_from_id)');
        $this->addSql('DROP INDEX unique_duplicated_group_event ON sheet_group');

    }
}
