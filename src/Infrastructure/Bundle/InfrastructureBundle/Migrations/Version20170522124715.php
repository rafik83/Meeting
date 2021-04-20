<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Change mass assignment from participant to user
 */
class Version20170522124715 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->addSql('DROP INDEX date_unique_idx ON mass_unavailability_assignment');
        $this->addSql('ALTER TABLE mass_unavailability_assignment ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mass_unavailability_assignment ADD CONSTRAINT FK_1A5777A3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('UPDATE `mass_unavailability_assignment`, `participant`
            SET `mass_unavailability_assignment`.`user_id` = `participant`.`user_id`
            WHERE `participant`.`id` = `mass_unavailability_assignment`.`participant_id`
        ');
        $this->addSql('CREATE INDEX IDX_1A5777A3A76ED395 ON mass_unavailability_assignment (user_id)');
        $this->addSql('ALTER TABLE mass_unavailability_assignment DROP FOREIGN KEY FK_1A5777A39D1C3019');
        $this->addSql('DROP INDEX IDX_1A5777A39D1C3019 ON mass_unavailability_assignment');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE mass_unavailability_assignment DROP FOREIGN KEY FK_1A5777A3A76ED395');
        $this->addSql('DROP INDEX IDX_1A5777A3A76ED395 ON mass_unavailability_assignment');
        $this->addSql('ALTER TABLE mass_unavailability_assignment DROP user_id');
        $this->addSql('ALTER TABLE mass_unavailability_assignment ADD CONSTRAINT FK_1A5777A39D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_1A5777A39D1C3019 ON mass_unavailability_assignment (participant_id)');
        $this->addSql('CREATE UNIQUE INDEX date_unique_idx ON mass_unavailability_assignment (participant_id, from_date, to_date)');
    }
}
