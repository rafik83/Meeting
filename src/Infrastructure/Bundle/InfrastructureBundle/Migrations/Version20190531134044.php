<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add Tip conditions and display properties
 */
class Version20190531134044 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tip ADD display VARCHAR(255) DEFAULT \'default\' NOT NULL, ADD condition_has_cart TINYINT(1) DEFAULT NULL, ADD condition_has_remaining_to_pay TINYINT(1) DEFAULT NULL, ADD condition_is_phone_confirmed TINYINT(1) DEFAULT NULL, ADD condition_is_complete_sheet TINYINT(1) DEFAULT NULL, ADD condition_has_pending_meeting_proposition TINYINT(1) DEFAULT NULL, ADD condition_on_orders LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tip DROP display, DROP condition_has_cart, DROP condition_has_remaining_to_pay, DROP condition_is_phone_confirmed, DROP condition_is_complete_sheet, DROP condition_has_pending_meeting_proposition, DROP condition_on_orders');
    }
}
