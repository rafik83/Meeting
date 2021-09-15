<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170202170600 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE mass_unavailability_assignment (id INT AUTO_INCREMENT NOT NULL, mass_id INT DEFAULT NULL, participant_id INT DEFAULT NULL, from_date DATETIME NOT NULL, to_date DATETIME NOT NULL, enabled TINYINT(1) DEFAULT \'1\' NOT NULL, INDEX IDX_1A5777A3EA5DA7EB (mass_id), INDEX IDX_1A5777A39D1C3019 (participant_id), UNIQUE INDEX date_unique_idx (participant_id, from_date, to_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mass_unavailability_assignment ADD CONSTRAINT FK_1A5777A3EA5DA7EB FOREIGN KEY (mass_id) REFERENCES mass_unavailability (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mass_unavailability_assignment ADD CONSTRAINT FK_1A5777A39D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE messaging_messages (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, subject VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, content LONGTEXT NOT NULL COLLATE utf8_unicode_ci, created_at DATETIME NOT NULL, INDEX IDX_E044CDE571F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP TABLE mass_unavailability_assignment');
        $this->addSql('ALTER TABLE participant ADD visio TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
