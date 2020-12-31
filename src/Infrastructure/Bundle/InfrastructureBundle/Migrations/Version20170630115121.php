<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create table catalog_visibility, catalog_visibility_types, catalog_visibility_categories
 * external_search_facet, external_search_facet_translation
 */
class Version20170630115121 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE catalog_visibility (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, UNIQUE INDEX catalog_visibility_event (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE catalog_visibility_types (catalog_visibility_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_EAB45ECACBF8E70D (catalog_visibility_id), INDEX IDX_EAB45ECAC54C8C93 (type_id), PRIMARY KEY(catalog_visibility_id, type_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE catalog_visibility_categories (catalog_visibility_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_AA20C193CBF8E70D (catalog_visibility_id), INDEX IDX_AA20C19312469DE2 (category_id), PRIMARY KEY(catalog_visibility_id, category_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE external_search_facet (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, enabled TINYINT(1) NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_AC855A1371F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE external_search_facet_translation (id INT AUTO_INCREMENT NOT NULL, search_facet_id INT DEFAULT NULL, `label` VARCHAR(255) DEFAULT NULL, placeholder VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_9FFFE80ECDF3491 (search_facet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE catalog_visibility ADD CONSTRAINT FK_E4A4B1DB71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE catalog_visibility_types ADD CONSTRAINT FK_EAB45ECACBF8E70D FOREIGN KEY (catalog_visibility_id) REFERENCES catalog_visibility (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE catalog_visibility_types ADD CONSTRAINT FK_EAB45ECAC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE catalog_visibility_categories ADD CONSTRAINT FK_AA20C193CBF8E70D FOREIGN KEY (catalog_visibility_id) REFERENCES catalog_visibility (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE catalog_visibility_categories ADD CONSTRAINT FK_AA20C19312469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE external_search_facet ADD CONSTRAINT FK_AC855A1371F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE external_search_facet_translation ADD CONSTRAINT FK_9FFFE80ECDF3491 FOREIGN KEY (search_facet_id) REFERENCES external_search_facet (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE catalog_visibility_types DROP FOREIGN KEY FK_EAB45ECACBF8E70D');
        $this->addSql('ALTER TABLE catalog_visibility_categories DROP FOREIGN KEY FK_AA20C193CBF8E70D');
        $this->addSql('ALTER TABLE external_search_facet_translation DROP FOREIGN KEY FK_9FFFE80ECDF3491');
        $this->addSql('DROP TABLE catalog_visibility');
        $this->addSql('DROP TABLE catalog_visibility_types');
        $this->addSql('DROP TABLE catalog_visibility_categories');
        $this->addSql('DROP TABLE external_search_facet');
        $this->addSql('DROP TABLE external_search_facet_translation');
    }
}
