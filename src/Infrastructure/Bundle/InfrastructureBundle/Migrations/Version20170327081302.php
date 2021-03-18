<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create table tip and tip_translation
 */
class Version20170327081302 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('CREATE TABLE tip (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, on_meeting_management TINYINT(1) NOT NULL, on_catalog TINYINT(1) NOT NULL, on_print_planning TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tip_translation (id INT AUTO_INCREMENT NOT NULL, tip_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_528D813B476C47F6 (tip_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tip_translation ADD CONSTRAINT FK_528D813B476C47F6 FOREIGN KEY (tip_id) REFERENCES tip (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE tip_translation DROP FOREIGN KEY FK_528D813B476C47F6');
        $this->addSql('DROP TABLE tip');
        $this->addSql('DROP TABLE tip_translation');
    }
}
