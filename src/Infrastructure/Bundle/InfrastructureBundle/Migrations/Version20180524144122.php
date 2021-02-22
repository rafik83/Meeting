<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add duplicatedFrom to Sheet model
 */
class Version20180524144122 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sheet ADD duplicated_from_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sheet ADD CONSTRAINT FK_873C91E2F0949296 FOREIGN KEY (duplicated_from_id) REFERENCES sheet (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_873C91E2F0949296 ON sheet (duplicated_from_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sheet DROP FOREIGN KEY FK_873C91E2F0949296');
        $this->addSql('DROP INDEX IDX_873C91E2F0949296 ON sheet');
        $this->addSql('ALTER TABLE sheet DROP duplicated_from_id');
    }
}
