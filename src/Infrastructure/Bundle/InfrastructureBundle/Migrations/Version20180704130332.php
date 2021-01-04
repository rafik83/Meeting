<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add CatalogFilterTag and CatalogFilterTagTranslation
 */
class Version20180704130332 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE catalog_tag_filter (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, tag VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_23AFE4EA71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE catalog_tag_filter_translation (id INT AUTO_INCREMENT NOT NULL, catalog_tag_filter_id INT NOT NULL, `label` VARCHAR(255) DEFAULT NULL, placeholder VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_3CB52307BE4F18F4 (catalog_tag_filter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE catalog_tag_filter ADD CONSTRAINT FK_23AFE4EA71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE catalog_tag_filter_translation ADD CONSTRAINT FK_3CB52307BE4F18F4 FOREIGN KEY (catalog_tag_filter_id) REFERENCES catalog_tag_filter (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE catalog_tag_filter_translation DROP FOREIGN KEY FK_3CB52307BE4F18F4');
        $this->addSql('DROP TABLE catalog_tag_filter');
        $this->addSql('DROP TABLE catalog_tag_filter_translation');
    }
}
