<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create table between FormTemplate and Type
 */
class Version20181121171557 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE template_form_type (form_template_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_D1AC2588F2B19FA9 (form_template_id), INDEX IDX_D1AC2588C54C8C93 (type_id), PRIMARY KEY(form_template_id, type_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE template_form_type ADD CONSTRAINT FK_D1AC2588F2B19FA9 FOREIGN KEY (form_template_id) REFERENCES template_form (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE template_form_type ADD CONSTRAINT FK_D1AC2588C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE template_form_type');
    }
}
