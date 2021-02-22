<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add linked_sheets table
 */
class Version20190319135640 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE linked_sheets (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_76E4139E71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE linked_sheets ADD CONSTRAINT FK_76E4139E71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sheet ADD linked_sheets_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sheet ADD CONSTRAINT FK_873C91E25744374F FOREIGN KEY (linked_sheets_id) REFERENCES linked_sheets (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_873C91E25744374F ON sheet (linked_sheets_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sheet DROP FOREIGN KEY FK_873C91E25744374F');
        $this->addSql('DROP INDEX IDX_873C91E25744374F ON sheet');
        $this->addSql('ALTER TABLE sheet DROP linked_sheets_id');
        $this->addSql('DROP TABLE linked_sheets');
    }
}
