<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add PlannerJob table
 */
class Version20170916192246 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE planner_job (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, admin_id INT NOT NULL, file_id INT DEFAULT NULL, solution_type VARCHAR(255) NOT NULL, lock_meeting_request TINYINT(1) NOT NULL, status VARCHAR(255) NOT NULL, error_message VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_47521C6F71F7E88B (event_id), INDEX IDX_47521C6F642B8210 (admin_id), INDEX IDX_47521C6F93CB796C (file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE planner_job ADD CONSTRAINT FK_47521C6F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE planner_job ADD CONSTRAINT FK_47521C6F642B8210 FOREIGN KEY (admin_id) REFERENCES admin (id)');
        $this->addSql('ALTER TABLE planner_job ADD CONSTRAINT FK_47521C6F93CB796C FOREIGN KEY (file_id) REFERENCES file (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE planner_job');
    }
}
