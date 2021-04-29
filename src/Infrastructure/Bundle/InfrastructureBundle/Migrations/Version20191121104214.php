<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add Answer and AnswerTranslation
 */
class Version20191121104214 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE registration_path_answer_translation (id INT AUTO_INCREMENT NOT NULL, answer_id INT DEFAULT NULL, locale VARCHAR(255) NOT NULL, title LONGTEXT NOT NULL, INDEX IDX_C9E60714AA334807 (answer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE registration_path_answer (id INT AUTO_INCREMENT NOT NULL, question_id INT DEFAULT NULL, INDEX IDX_F9CC74FB1E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE registration_path_answer_translation ADD CONSTRAINT FK_C9E60714AA334807 FOREIGN KEY (answer_id) REFERENCES registration_path_answer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE registration_path_answer ADD CONSTRAINT FK_F9CC74FB1E27F6BF FOREIGN KEY (question_id) REFERENCES registration_path_question (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE registration_path_answer_translation DROP FOREIGN KEY FK_C9E60714AA334807');
        $this->addSql('DROP TABLE registration_path_answer_translation');
        $this->addSql('DROP TABLE registration_path_answer');
    }
}
