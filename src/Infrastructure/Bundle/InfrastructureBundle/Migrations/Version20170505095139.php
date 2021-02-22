<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Drop Participant and add User and Event on Unavailability
 */
class Version20170505095139 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // Create unavailability_backup table
        $this->addSql('CREATE TABLE unavailability_backup LIKE unavailability');
        $this->addSql('INSERT unavailability_backup SELECT * FROM unavailability');

        // Create user_id and event_id fields in unavailability table
        $this->addSql('ALTER TABLE unavailability ADD user_id INT NOT NULL, ADD event_id INT NOT NULL');

        // Migrate Participant -> Event + User
        $this->addSql('
            UPDATE `unavailability`, `participant`, `sheet`
            SET `unavailability`.`user_id` = `participant`.`user_id`, `unavailability`.`event_id` = `sheet`.`event_id`
            WHERE `participant`.`id` = `unavailability`.`participant_id` AND `participant`.`sheet_id` = `sheet`.`id`
        ');

        // Add constraints and indexes on user_id and event_id
        $this->addSql('ALTER TABLE unavailability ADD CONSTRAINT FK_F0016D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE unavailability ADD CONSTRAINT FK_F0016D171F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_F0016D1A76ED395 ON unavailability (user_id)');
        $this->addSql('CREATE INDEX IDX_F0016D171F7E88B ON unavailability (event_id)');

        // Drop Participant
        $this->addSql('ALTER TABLE unavailability DROP FOREIGN KEY FK_F0016D19D1C3019');
        $this->addSql('DROP INDEX IDX_F0016D19D1C3019 ON unavailability');
        $this->addSql('ALTER TABLE unavailability DROP participant_id');

        // Everything ok, drop unavailability_backup
        $this->addSql('DROP TABLE unavailability_backup');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // Recreate unavailability from unavailability_backup
        $this->addSql('DROP TABLE unavailability');
        $this->addSql('CREATE TABLE unavailability LIKE unavailability_backup');
        $this->addSql('INSERT unavailability SELECT * FROM unavailability_backup');
        $this->addSql('DROP TABLE unavailability_backup');
    }
}
