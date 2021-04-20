<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add User to entity Speaker
 */
class Version20200316103748 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE happening_speaker ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE happening_speaker ADD CONSTRAINT FK_43E5DFEFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_43E5DFEFA76ED395 ON happening_speaker (user_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE happening_speaker DROP FOREIGN KEY FK_43E5DFEFA76ED395');
        $this->addSql('DROP INDEX IDX_43E5DFEFA76ED395 ON happening_speaker');
        $this->addSql('ALTER TABLE happening_speaker DROP user_id');
    }
}
