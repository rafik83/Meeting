<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create table messaging_message_translation for Message translation
 */
class Version20170407152027 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE messaging_message_translation (id INT AUTO_INCREMENT NOT NULL, message_id INT DEFAULT NULL, subject VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, locale VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_72DF2E88537A1329 (message_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE messaging_message_translation ADD CONSTRAINT FK_72DF2E88537A1329 FOREIGN KEY (message_id) REFERENCES messaging_message (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE messaging_message_translation');
    }
}
