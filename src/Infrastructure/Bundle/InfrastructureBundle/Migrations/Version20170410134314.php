<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add closeMeetingRequestDate and closeAnsweringMeetingRequestDate to Event
 */
class Version20170410134314 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE event ADD configuration_close_meeting_request_date DATETIME DEFAULT NULL, ADD configuration_close_answering_meeting_request_date DATETIME DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE event DROP configuration_close_meeting_request_date, DROP configuration_close_answering_meeting_request_date');
    }
}
