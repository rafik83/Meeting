<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migrate UserEventExtraData of Sheet owner "protected_key" to SheetExtraData
 */
final class Version20181005093000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql(
            'INSERT INTO sheet_extra_data (
                sheet_id,
                name,
                value,
                created_at,
                updated_at
            )
            SELECT
                sheet.id,
                \'protected_key\',
                user_event_extra_data.value,
                user_event_extra_data.created_at,
                user_event_extra_data.updated_at
            FROM sheet
            INNER JOIN user ON user.id = sheet.owner_id
            INNER JOIN user_event_extra_data
                ON user_event_extra_data.name = \'protected_key\'
                AND user_event_extra_data.user_id = user.id
                AND user_event_extra_data.event_id = sheet.event_id
            '
        );
    }

    public function down(Schema $schema): void
    {
    }
}
