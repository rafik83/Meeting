<?php declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create vote table for webinar questions
 */
final class Version20200724124429 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE happening_question_vote (id INT AUTO_INCREMENT NOT NULL, question_id INT DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_C49AF8C81E27F6BF (question_id), INDEX IDX_C49AF8C8A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE happening_question_vote ADD CONSTRAINT FK_C49AF8C81E27F6BF FOREIGN KEY (question_id) REFERENCES happening_question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE happening_question_vote ADD CONSTRAINT FK_C49AF8C8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE happening_question_vote');
    }
}
