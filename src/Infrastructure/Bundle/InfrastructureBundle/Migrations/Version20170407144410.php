<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add sheet group entity ("Entité multi-fiches")
 */
class Version20170407144410 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sheet_group (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, manager_id INT NOT NULL, title LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_E95566C71F7E88B (event_id), INDEX IDX_E95566C783E3463 (manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sheet_group ADD CONSTRAINT FK_E95566C71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sheet_group ADD CONSTRAINT FK_E95566C783E3463 FOREIGN KEY (manager_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX unique_manager_event ON sheet_group (event_id, manager_id)');
        $this->addSql('ALTER TABLE sheet ADD group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sheet ADD CONSTRAINT FK_873C91E2FE54D947 FOREIGN KEY (group_id) REFERENCES sheet_group (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_873C91E2FE54D947 ON sheet (group_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sheet DROP FOREIGN KEY FK_873C91E2FE54D947');
        $this->addSql('DROP INDEX IDX_873C91E2FE54D947 ON sheet');
        $this->addSql('ALTER TABLE sheet DROP group_id');
        $this->addSql('DROP INDEX unique_manager_event ON sheet_group');
        $this->addSql('DROP TABLE sheet_group');
    }
}
