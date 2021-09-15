<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * This migration changes the column position to name it rank for the happening category
 * And it creates also the unavailability category
 */
class Version20161208131753 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE unavailability_category (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, picto VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, left_color VARCHAR(255) NOT NULL, right_color VARCHAR(255) NOT NULL, INDEX IDX_607283A271F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE unavailability_category ADD CONSTRAINT FK_607283A271F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE happening_category CHANGE position rank INT NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE unavailability_category');
        $this->addSql('ALTER TABLE happening_category CHANGE rank position INT NOT NULL');
    }
}
