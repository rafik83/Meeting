<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add user_id on happening_participation
 */
class Version20170522124244 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE happening_participation DROP FOREIGN KEY FK_6B31720C9D1C3019');
        $this->addSql('DROP INDEX unique_idx_1 ON happening_participation');
        $this->addSql('DROP INDEX IDX_6B31720C9D1C3019 ON happening_participation');

        $this->addSql('ALTER TABLE happening_participation ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE happening_participation ADD CONSTRAINT FK_6B31720CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');

        $this->addSql('CREATE UNIQUE INDEX unique_happening_user ON happening_participation (happening_id, user_id)');

        // IGNORE duplicate entry (happening_id, user_id)
        $this->addSql('UPDATE IGNORE happening_participation hp JOIN participant p ON hp.participant_ID = p.id SET hp.user_id = p.user_id');
        // Remove happening_participation where user_id is null due to duplicate entry
        $this->addSql('DELETE FROM `happening_participation` WHERE `user_id` IS NULL');

        $this->addSql('CREATE INDEX IDX_6B31720CA76ED395 ON happening_participation (user_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX unique_happening_user ON happening_participation');
        $this->addSql('ALTER TABLE happening_participation DROP FOREIGN KEY FK_6B31720CA76ED395');
        $this->addSql('DROP INDEX IDX_6B31720CA76ED395 ON happening_participation');
        $this->addSql('ALTER TABLE happening_participation DROP user_id');
    }
}
