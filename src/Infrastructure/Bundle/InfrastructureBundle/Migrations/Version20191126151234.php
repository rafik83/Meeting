<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add previous_answer_id in Question
 */
class Version20191126151234 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE registration_path_question ADD previous_answer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE registration_path_question ADD CONSTRAINT FK_682D9F65AD416706 FOREIGN KEY (previous_answer_id) REFERENCES registration_path_answer (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_682D9F65AD416706 ON registration_path_question (previous_answer_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE registration_path_question DROP FOREIGN KEY FK_682D9F65AD416706');
        $this->addSql('DROP INDEX IDX_682D9F65AD416706 ON registration_path_question');
        $this->addSql('ALTER TABLE registration_path_question DROP previous_answer_id');
    }
}
