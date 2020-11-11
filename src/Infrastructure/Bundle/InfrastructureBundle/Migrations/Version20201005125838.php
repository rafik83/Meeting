<?php declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201005125838 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE chat_session (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, from_user_id INT DEFAULT NULL, to_user_id INT DEFAULT NULL, INDEX IDX_9C4A19BD71F7E88B (event_id), INDEX IDX_9C4A19BD2130303A (from_user_id), INDEX IDX_9C4A19BD29F6EE60 (to_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE chat_session ADD CONSTRAINT FK_9C4A19BD71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chat_session ADD CONSTRAINT FK_9C4A19BD2130303A FOREIGN KEY (from_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chat_session ADD CONSTRAINT FK_9C4A19BD29F6EE60 FOREIGN KEY (to_user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE chat_session');
    }
}
