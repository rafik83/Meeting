<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add registration_path_question and registration_path_question_translation tables
 */
class Version20191120152439 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE registration_path_question_translation (id INT AUTO_INCREMENT NOT NULL, question_id INT DEFAULT NULL, locale VARCHAR(255) NOT NULL, title LONGTEXT NOT NULL, INDEX IDX_4DD242C71E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE registration_path_question (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, INDEX IDX_682D9F6571F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE registration_path_question_translation ADD CONSTRAINT FK_4DD242C71E27F6BF FOREIGN KEY (question_id) REFERENCES registration_path_question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE registration_path_question ADD CONSTRAINT FK_682D9F6571F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE registration_path_question_translation DROP FOREIGN KEY FK_4DD242C71E27F6BF');
        $this->addSql('DROP TABLE registration_path_question_translation');
        $this->addSql('DROP TABLE registration_path_question');
    }
}
