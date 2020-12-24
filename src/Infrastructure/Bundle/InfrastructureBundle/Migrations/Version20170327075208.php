<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Drop BillingInfo on Order
 */
class Version20170327075208 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE _order DROP vat_mode, DROP vat_applicable, DROP billing_info_last_name, DROP billing_info_first_name, DROP billing_info_position, DROP billing_info_phone, DROP billing_info_mobile, DROP billing_info_email, DROP billing_info_company, DROP billing_info_vat_number, DROP billing_info_address_street, DROP billing_info_address_zipcode, DROP billing_info_address_city, DROP billing_info_address_country, DROP billing_info_gender, DROP billing_info_reference');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE _order ADD vat_mode VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD vat_applicable TINYINT(1) NOT NULL, ADD billing_info_last_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD billing_info_first_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD billing_info_position VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD billing_info_phone VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD billing_info_mobile VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD billing_info_email VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD billing_info_company VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD billing_info_vat_number VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD billing_info_address_street VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD billing_info_address_zipcode VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD billing_info_address_city VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD billing_info_address_country VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD billing_info_gender VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD billing_info_reference VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
