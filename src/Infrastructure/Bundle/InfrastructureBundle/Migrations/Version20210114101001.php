<?php declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Rename move meeting permission to update
 */
final class Version20210114101001 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE type ADD can_update_meeting TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('UPDATE type SET can_update_meeting = can_move_meeting');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE type SET can_move_meeting = can_update_meeting');
        $this->addSql('ALTER TABLE type DROP can_update_meeting');
    }
}
