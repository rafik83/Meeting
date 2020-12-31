<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add user account fields
 */
class Version20160919122927 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD account_company_address VARCHAR(255) DEFAULT NULL, ADD account_company_zip_code VARCHAR(255) DEFAULT NULL, ADD account_company_city VARCHAR(255) DEFAULT NULL, ADD account_company_country VARCHAR(255) DEFAULT NULL, ADD account_company_website VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP account_company_address, DROP account_company_zip_code, DROP account_company_city, DROP account_company_country, DROP account_company_website');
    }
}
