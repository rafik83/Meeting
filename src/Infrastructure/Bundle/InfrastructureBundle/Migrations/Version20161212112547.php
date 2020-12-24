<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * This migration add:
 *  - mass unavailability entity
 *  - mass unavailability translation entity
 */
class Version20161212112547 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE mass_unavailability (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, category_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, begin DATETIME NOT NULL, end DATETIME NOT NULL, blocking TINYINT(1) DEFAULT \'1\' NOT NULL, INDEX IDX_4B1C677871F7E88B (event_id), INDEX IDX_4B1C677812469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mass_unavailability_translation (id INT AUTO_INCREMENT NOT NULL, mass_id INT DEFAULT NULL, title LONGTEXT NOT NULL, description LONGTEXT DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_D0287F9CEA5DA7EB (mass_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mass_unavailability ADD CONSTRAINT FK_4B1C677871F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mass_unavailability ADD CONSTRAINT FK_4B1C677812469DE2 FOREIGN KEY (category_id) REFERENCES unavailability_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mass_unavailability_translation ADD CONSTRAINT FK_D0287F9CEA5DA7EB FOREIGN KEY (mass_id) REFERENCES mass_unavailability (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE mass_unavailability_translation DROP FOREIGN KEY FK_D0287F9CEA5DA7EB');
        $this->addSql('DROP TABLE mass_unavailability');
        $this->addSql('DROP TABLE mass_unavailability_translation');
    }
}
