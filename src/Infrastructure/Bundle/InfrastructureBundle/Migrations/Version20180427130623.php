<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180427130623 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_event_authentication_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, event_id INT NOT NULL, token VARCHAR(255) NOT NULL, expired_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_D6B1D4BD5F37A13B (token), INDEX IDX_D6B1D4BDA76ED395 (user_id), INDEX IDX_D6B1D4BD71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_event_authentication_token ADD CONSTRAINT FK_D6B1D4BDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_event_authentication_token ADD CONSTRAINT FK_D6B1D4BD71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user_event_authentication_token');
    }
}
