<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add FormTemplate and FormTemplateTranslation
 */
class Version20181114163507 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE template_form (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, title VARCHAR(255) NOT NULL, locales LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', fallback VARCHAR(5) NOT NULL, value LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', created_at DATETIME NOT NULL, INDEX IDX_8746D72271F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE template_form_translation (id INT AUTO_INCREMENT NOT NULL, form_template_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_A92A55AFF2B19FA9 (form_template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE template_form ADD CONSTRAINT FK_8746D72271F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE template_form_translation ADD CONSTRAINT FK_A92A55AFF2B19FA9 FOREIGN KEY (form_template_id) REFERENCES template_form_translation (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE template_form_translation DROP FOREIGN KEY FK_A92A55AFF2B19FA9');
        $this->addSql('CREATE TABLE command_migrations (version VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, PRIMARY KEY(version)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP TABLE template_form');
        $this->addSql('DROP TABLE template_form_translation');
    }
}
