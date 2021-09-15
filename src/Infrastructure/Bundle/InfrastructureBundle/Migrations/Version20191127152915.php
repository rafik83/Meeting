<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add registration_path_answer_type table
 */
class Version20191127152915 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE registration_path_answer_type (answer_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_57894465AA334807 (answer_id), INDEX IDX_57894465C54C8C93 (type_id), PRIMARY KEY(answer_id, type_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE registration_path_answer_type ADD CONSTRAINT FK_57894465AA334807 FOREIGN KEY (answer_id) REFERENCES registration_path_answer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE registration_path_answer_type ADD CONSTRAINT FK_57894465C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE registration_path_answer_type');
    }
}
