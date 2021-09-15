<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migration participant visio to user_event_extra_data
 */
class Version20180921091926 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->addSql(
            "INSERT INTO user_event_extra_data (user_id, event_id, `name`, value, created_at, updated_at)
                SELECT participant.user_id, sheet.event_id, 'is_participant_visio', 'true', now(), now()
                FROM participant
                INNER JOIN sheet ON participant.sheet_id = sheet.id
                WHERE participant.visio = 1"
        );
        $this->addSql('ALTER TABLE participant DROP visio');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
    }
}
