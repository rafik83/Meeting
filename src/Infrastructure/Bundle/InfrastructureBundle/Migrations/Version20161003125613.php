<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20161003125613 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE admin_type (admin_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_ACC7D5C9642B8210 (admin_id), INDEX IDX_ACC7D5C9C54C8C93 (type_id), PRIMARY KEY(admin_id, type_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE boolean_template_filter (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, template_key VARCHAR(255) NOT NULL, `label` VARCHAR(255) NOT NULL, INDEX IDX_5F4E5D4971F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE admin_type ADD CONSTRAINT FK_ACC7D5C9642B8210 FOREIGN KEY (admin_id) REFERENCES admin (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin_type ADD CONSTRAINT FK_ACC7D5C9C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE boolean_template_filter ADD CONSTRAINT FK_5F4E5D4971F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE _order ADD cancelled TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE participant ADD registration_step INT DEFAULT NULL, ADD registration_complete TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE sheet ADD completeness INT NOT NULL, DROP completed');
        $this->addSql('ALTER TABLE type ADD hidden TINYINT(1) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DROP TABLE admin_type');
        $this->addSql('DROP TABLE boolean_template_filter');
        $this->addSql('ALTER TABLE _order DROP cancelled');
        $this->addSql('ALTER TABLE participant DROP registration_step, DROP registration_complete');
        $this->addSql('ALTER TABLE sheet ADD completed TINYINT(1) NOT NULL, DROP completeness');
        $this->addSql('ALTER TABLE type DROP hidden');
    }
}
