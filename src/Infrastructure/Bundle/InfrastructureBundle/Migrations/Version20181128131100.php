<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Fix mapping, update ManyToMany relation between FormTemplate (template_form) and Type
 */
class Version20181128131100 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE template_form_type DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE template_form_type ADD PRIMARY KEY (type_id, form_template_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE template_form_type DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE template_form_type ADD PRIMARY KEY (form_template_id, type_id)');
    }
}
