<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add Event on Tip
 */
class Version20171130095742 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tip ADD event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tip ADD CONSTRAINT FK_4883B84C71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4883B84C71F7E88B ON tip (event_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tip DROP FOREIGN KEY FK_4883B84C71F7E88B');
        $this->addSql('DROP INDEX IDX_4883B84C71F7E88B ON tip');
        $this->addSql('ALTER TABLE tip DROP event_id');
    }
}
