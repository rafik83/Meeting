<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create table catalog_visibility
 */
class Version20170623150028 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE catalog_visibility (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, INDEX IDX_E4A4B1DB71F7E88B (event_id), UNIQUE INDEX catalog_visibility_event (id, event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE catalog_visibility_types (type_id INT NOT NULL, catalog_id INT NOT NULL, INDEX IDX_EAB45ECAC54C8C93 (type_id), INDEX IDX_EAB45ECACC3C66FC (catalog_id), PRIMARY KEY(type_id, catalog_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE catalog_visibility_categories (category_id INT NOT NULL, catalog_id INT NOT NULL, INDEX IDX_AA20C19312469DE2 (category_id), INDEX IDX_AA20C193CC3C66FC (catalog_id), PRIMARY KEY(category_id, catalog_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE catalog_visibility ADD CONSTRAINT FK_E4A4B1DB71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE catalog_visibility_types ADD CONSTRAINT FK_EAB45ECAC54C8C93 FOREIGN KEY (type_id) REFERENCES catalog_visibility (id)');
        $this->addSql('ALTER TABLE catalog_visibility_types ADD CONSTRAINT FK_EAB45ECACC3C66FC FOREIGN KEY (catalog_id) REFERENCES type (id)');
        $this->addSql('ALTER TABLE catalog_visibility_categories ADD CONSTRAINT FK_AA20C19312469DE2 FOREIGN KEY (category_id) REFERENCES catalog_visibility (id)');
        $this->addSql('ALTER TABLE catalog_visibility_categories ADD CONSTRAINT FK_AA20C193CC3C66FC FOREIGN KEY (catalog_id) REFERENCES category (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE catalog_visibility_types DROP FOREIGN KEY FK_EAB45ECAC54C8C93');
        $this->addSql('ALTER TABLE catalog_visibility_categories DROP FOREIGN KEY FK_AA20C19312469DE2');
        $this->addSql('DROP TABLE catalog_visibility');
        $this->addSql('DROP TABLE catalog_visibility_types');
        $this->addSql('DROP TABLE catalog_visibility_categories');
    }
}
