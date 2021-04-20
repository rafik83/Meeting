<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add Analytic/MeetingSolution table
 */
class Version20170731125827 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE analytic_meeting_solution (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, meetings INT NOT NULL, requests INT NOT NULL, filling_rate INT NOT NULL, sheet_satisfaction LONGTEXT NOT NULL, spot_satisfaction LONGTEXT NOT NULL, spot_filling_graph LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_1D6086571F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE analytic_meeting_solution ADD CONSTRAINT FK_1D6086571F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE analytic_meeting_solution');
    }
}
