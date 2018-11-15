<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create table FormTemplate - Type
 */
class Version20181115151123 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE form_template_type (form_template_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_FAC63A7BF2B19FA9 (form_template_id), INDEX IDX_FAC63A7BC54C8C93 (type_id), PRIMARY KEY(form_template_id, type_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE form_template_type ADD CONSTRAINT FK_FAC63A7BF2B19FA9 FOREIGN KEY (form_template_id) REFERENCES template_form (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE form_template_type ADD CONSTRAINT FK_FAC63A7BC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE form_template_type');
    }
}
