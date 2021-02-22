<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add Sheet/FormData and User/FormData
 */
class Version20181121162222 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sheet_form_data (sheet_id INT NOT NULL, form_template_id INT NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', INDEX IDX_5644F168B1206A5 (sheet_id), INDEX IDX_5644F16F2B19FA9 (form_template_id), PRIMARY KEY(sheet_id, form_template_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_form_data (user_id INT NOT NULL, form_template_id INT NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', INDEX IDX_39327A90A76ED395 (user_id), INDEX IDX_39327A90F2B19FA9 (form_template_id), PRIMARY KEY(user_id, form_template_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sheet_form_data ADD CONSTRAINT FK_5644F168B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sheet_form_data ADD CONSTRAINT FK_5644F16F2B19FA9 FOREIGN KEY (form_template_id) REFERENCES template_form (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_form_data ADD CONSTRAINT FK_39327A90A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_form_data ADD CONSTRAINT FK_39327A90F2B19FA9 FOREIGN KEY (form_template_id) REFERENCES template_form (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sheet_form_data');
        $this->addSql('DROP TABLE user_form_data');
    }
}
