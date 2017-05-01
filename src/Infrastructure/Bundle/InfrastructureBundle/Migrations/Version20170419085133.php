<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add aggregates on participant for hasRequestAssigned and isFullyUnavailable
 */
class Version20170419085133 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE participant ADD has_request_assigned TINYINT(1) DEFAULT \'0\' NOT NULL, ADD is_fully_unavailable TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE participant DROP has_request_assigned, DROP is_fully_unavailable');
    }
}
