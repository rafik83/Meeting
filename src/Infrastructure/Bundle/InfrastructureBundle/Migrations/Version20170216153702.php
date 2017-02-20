<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add reference field to BillingInfo
 */
class Version20170216153702 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE billing_info ADD reference VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event DROP invoice_logo, DROP invoice_logo_extension');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE billing_info DROP reference');
        $this->addSql('ALTER TABLE event ADD invoice_logo VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD invoice_logo_extension VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
