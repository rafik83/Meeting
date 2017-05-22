<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Remove participant_id on happening_participation
 */
class Version20170522133520 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX unique_idx_1 ON happening_participation');
        $this->addSql('ALTER TABLE happening_participation DROP participant_id');
        $this->addSql('CREATE UNIQUE INDEX unique_idx_1 ON happening_participation (happening_id, user_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX unique_idx_1 ON happening_participation');
        $this->addSql('ALTER TABLE happening_participation ADD participant_id INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX unique_idx_1 ON happening_participation (happening_id, participant_id)');
    }
}
