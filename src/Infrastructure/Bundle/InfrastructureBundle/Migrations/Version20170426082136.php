<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add hasMessage aggregate to Meeting Request
 */
class Version20170426082136 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE meeting_request ADD has_message TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE meeting_request DROP has_message');
    }

    /**
     * {@inheritdoc}
     */
    public function postUp(Schema $schema)
    {
        $this->connection->executeQuery(
            'UPDATE meeting_request
             SET has_message = 1
             WHERE meeting_request.id IN (SELECT DISTINCT m.request_id FROM meeting_message m)
             '
        );
    }
}
