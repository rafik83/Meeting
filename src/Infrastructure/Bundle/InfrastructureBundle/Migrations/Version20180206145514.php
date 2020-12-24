<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add vatRate on Order/PromotionCode
 */
class Version20180206145514 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_promotion_code ADD vat_rate DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('UPDATE order_promotion_code INNER JOIN _order ON _order.id = order_promotion_code.order_id SET order_promotion_code.vat_rate = _order.vat_rate');
        $this->addSql('ALTER TABLE order_promotion_code CHANGE vat_rate vat_rate DOUBLE PRECISION NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_promotion_code DROP vat_rate');
    }
}
