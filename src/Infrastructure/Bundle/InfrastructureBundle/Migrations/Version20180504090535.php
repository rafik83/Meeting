<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Set unique constraint on event and token
 */
class Version20180504090535 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_D6B1D4BD5F37A13B ON user_event_authentication_token');
        $this->addSql('CREATE UNIQUE INDEX unique_authentication_token_event ON user_event_authentication_token (token, event_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX unique_authentication_token_event ON user_event_authentication_token');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D6B1D4BD5F37A13B ON user_event_authentication_token (token)');
    }
}
