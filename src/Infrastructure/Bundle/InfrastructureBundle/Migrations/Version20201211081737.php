<?php declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add fields for question reply
 */
final class Version20201211081737 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE happening_question ADD replied_by_id INT DEFAULT NULL, ADD reply_content VARCHAR(300) DEFAULT NULL, ADD replied_at DATETIME DEFAULT NULL, CHANGE content content VARCHAR(300) NOT NULL');
        $this->addSql('ALTER TABLE happening_question ADD CONSTRAINT FK_BCCF876DD6FBBEB5 FOREIGN KEY (replied_by_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_BCCF876DD6FBBEB5 ON happening_question (replied_by_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE happening_question DROP FOREIGN KEY FK_BCCF876DD6FBBEB5');
        $this->addSql('DROP INDEX IDX_BCCF876DD6FBBEB5 ON happening_question');
        $this->addSql('ALTER TABLE happening_question DROP replied_by_id, DROP reply_content, DROP replied_at, CHANGE content content VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
    }
}
