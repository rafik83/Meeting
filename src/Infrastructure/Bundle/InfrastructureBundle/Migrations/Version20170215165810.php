<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170215165810 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE invoice_prefix (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, prefix VARCHAR(255) DEFAULT NULL, is_default TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event ADD invoice_prefix_id INT DEFAULT NULL');
        $this->addSql("INSERT INTO invoice_prefix VALUES (1, 'Vimeet', 'Vi', 1)");
        $this->addSql('UPDATE event SET invoice_prefix_id = 1');
        $this->addSql('ALTER TABLE event MODIFY invoice_prefix_id INT NOT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA739FABE62 FOREIGN KEY (invoice_prefix_id) REFERENCES invoice_prefix (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_3BAE0AA739FABE62 ON event (invoice_prefix_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA739FABE62');
        $this->addSql('DROP INDEX IDX_3BAE0AA739FABE62 ON event');
        $this->addSql('ALTER TABLE event DROP invoice_prefix_id');
        $this->addSql('DROP TABLE invoice_prefix');
    }
}
