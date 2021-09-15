<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add contact entity
 */
class Version20190424152031 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE contact (event_id INT NOT NULL, user_id INT NOT NULL, contact_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_4C62E63871F7E88B (event_id), INDEX IDX_4C62E638A76ED395 (user_id), INDEX IDX_4C62E638E7A1254A (contact_id), PRIMARY KEY(event_id, user_id, contact_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E63871F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638E7A1254A FOREIGN KEY (contact_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE contact');
    }
}
