<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add vatRate on Order\Row
 */
class Version20180206142534 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_row ADD vat_rate DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('UPDATE order_row INNER JOIN _order ON _order.id = order_row.order_id SET order_row.vat_rate = _order.vat_rate');
        $this->addSql('ALTER TABLE order_row CHANGE vat_rate vat_rate DOUBLE PRECISION NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_row DROP vat_rate');
    }
}
