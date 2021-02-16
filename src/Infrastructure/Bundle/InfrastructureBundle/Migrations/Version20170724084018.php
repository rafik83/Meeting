<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create table catalog_visibility_translation and map it to catalog_visibility
 */
class Version20170724084018 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE catalog_visibility_translation (id INT AUTO_INCREMENT NOT NULL, catalog_visibility_id INT NOT NULL, title VARCHAR(255) NOT NULL, content VARCHAR(255) NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_9E96BD7CCBF8E70D (catalog_visibility_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE catalog_visibility_translation ADD CONSTRAINT FK_9E96BD7CCBF8E70D FOREIGN KEY (catalog_visibility_id) REFERENCES catalog_visibility (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE catalog_visibility ADD has_message TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE catalog_visibility_translation');
        $this->addSql('ALTER TABLE catalog_visibility DROP has_message');
    }
}
