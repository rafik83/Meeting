<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add `reference`field to `billing_info`
 * Add `billing_info_reference` to `_order`
 */
class Version20170216153702 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE billing_info ADD reference VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE _order ADD billing_info_reference VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE billing_info DROP reference');
        $this->addSql('ALTER TABLE _order DROP billing_info_reference');
    }
}
