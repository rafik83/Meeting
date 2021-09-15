<?php declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add chat message entity
 */
final class Version20200820075756 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE chat_message (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, object_type VARCHAR(255) NOT NULL, object_id INT NOT NULL, created_at DATETIME NOT NULL, content LONGTEXT NOT NULL, INDEX IDX_FAB3FC16B03A8386 (created_by_id), INDEX object_index (object_type, object_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE chat_message');
    }
}
