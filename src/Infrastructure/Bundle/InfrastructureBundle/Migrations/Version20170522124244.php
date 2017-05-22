<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170522124244 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE happening_participation ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE happening_participation ADD CONSTRAINT FK_6B31720CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_6B31720CA76ED395 ON happening_participation (user_id)');
        $this->addSql('UPDATE happening_participation hp JOIN participant p ON hp.participant_ID = p.id SET hp.user_id = p.user_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE happening_participation DROP FOREIGN KEY FK_6B31720CA76ED395');
        $this->addSql('DROP INDEX IDX_6B31720CA76ED395 ON happening_participation');
        $this->addSql('ALTER TABLE happening_participation DROP user_id');
    }
}
