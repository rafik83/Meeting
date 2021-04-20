<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add template_form and template_form_translation tables
 */
class Version20181115144240 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE template_form (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, title VARCHAR(255) NOT NULL, locales LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', fallback VARCHAR(5) NOT NULL, value LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', created_at DATETIME NOT NULL, published TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_8746D72271F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE template_form_translation (id INT AUTO_INCREMENT NOT NULL, form_template_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_A92A55AFF2B19FA9 (form_template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE template_form ADD CONSTRAINT FK_8746D72271F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE template_form_translation ADD CONSTRAINT FK_A92A55AFF2B19FA9 FOREIGN KEY (form_template_id) REFERENCES template_form (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE template_form_translation');
        $this->addSql('DROP TABLE template_form');
    }
}
