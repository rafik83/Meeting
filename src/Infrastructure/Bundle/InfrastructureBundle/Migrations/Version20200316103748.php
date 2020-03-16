<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20200316103748 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE happening_speaker ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE happening_speaker ADD CONSTRAINT FK_43E5DFEFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_43E5DFEFA76ED395 ON happening_speaker (user_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE happening_speaker DROP FOREIGN KEY FK_43E5DFEFA76ED395');
        $this->addSql('DROP INDEX IDX_43E5DFEFA76ED395 ON happening_speaker');
        $this->addSql('ALTER TABLE happening_speaker DROP user_id');
    }
}
