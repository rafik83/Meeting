<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add mass unavailability assignment table
 */
class Version20170202093213 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE mass_unavailability_assignment (id INT AUTO_INCREMENT NOT NULL, mass_id INT DEFAULT NULL, participant_id INT DEFAULT NULL, from_date DATETIME NOT NULL, to_date DATETIME NOT NULL, INDEX IDX_1A5777A3EA5DA7EB (mass_id), INDEX IDX_1A5777A39D1C3019 (participant_id), UNIQUE INDEX date_unique_idx (participant_id, from_date, to_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mass_unavailability_assignment ADD CONSTRAINT FK_1A5777A3EA5DA7EB FOREIGN KEY (mass_id) REFERENCES mass_unavailability (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mass_unavailability_assignment ADD CONSTRAINT FK_1A5777A39D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE mass_unavailability_assignment');
    }
}
