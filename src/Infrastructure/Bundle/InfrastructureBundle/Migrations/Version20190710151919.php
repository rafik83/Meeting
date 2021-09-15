<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add promotion_code_group
 */
class Version20190710151919 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE promotion_code_group (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_C43B762D71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE promotion_code_group ADD CONSTRAINT FK_C43B762D71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE promotion_code ADD promotion_code_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE promotion_code ADD CONSTRAINT FK_C1EFB8079EBA2713 FOREIGN KEY (promotion_code_group_id) REFERENCES promotion_code_group (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_C1EFB8079EBA2713 ON promotion_code (promotion_code_group_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE promotion_code_group');
        $this->addSql('ALTER TABLE promotion_code DROP FOREIGN KEY FK_C1EFB8079EBA2713');
        $this->addSql('DROP INDEX IDX_C1EFB8079EBA2713 ON promotion_code');
        $this->addSql('ALTER TABLE promotion_code DROP promotion_code_group_id');
    }
}
