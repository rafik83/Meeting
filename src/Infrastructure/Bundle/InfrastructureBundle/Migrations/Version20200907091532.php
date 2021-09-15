<?php declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add vote entity for chat messages
 */
final class Version20200907091532 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE chat_message_vote (id INT AUTO_INCREMENT NOT NULL, chat_message_id INT DEFAULT NULL, user_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_D6E773C948B568F (chat_message_id), INDEX IDX_D6E773CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE chat_message_vote ADD CONSTRAINT FK_D6E773C948B568F FOREIGN KEY (chat_message_id) REFERENCES chat_message (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chat_message_vote ADD CONSTRAINT FK_D6E773CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE chat_message_vote');
    }
}
