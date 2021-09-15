<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add meeting_participant_extra_data table
 */
final class Version20180827093424 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE meeting_participant_extra_data (id INT AUTO_INCREMENT NOT NULL, participant_id INT NOT NULL, meeting_id INT NOT NULL, date DATETIME DEFAULT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_6648EEDC9D1C3019 (participant_id), INDEX IDX_6648EEDC67433D9C (meeting_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE meeting_participant_extra_data ADD CONSTRAINT FK_6648EEDC9D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_participant_extra_data ADD CONSTRAINT FK_6648EEDC67433D9C FOREIGN KEY (meeting_id) REFERENCES meeting (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE meeting_participant_extra_data');
    }
}
