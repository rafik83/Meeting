<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Change is_created_by_participants by created_type on meeting table
 */
class Version20181012085832 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE meeting ADD created_type VARCHAR(255) NOT NULL');
        $this->addSql("UPDATE meeting SET created_type = 'participant' WHERE is_created_by_participants = 1");
        $this->addSql("UPDATE meeting SET created_type = 'planner' WHERE is_created_by_participants = 0");
        $this->addSql('ALTER TABLE meeting DROP is_created_by_participants');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE meeting ADD is_created_by_participants TINYINT(1) DEFAULT \'0\' NOT NULL, DROP created_type');
    }
}
