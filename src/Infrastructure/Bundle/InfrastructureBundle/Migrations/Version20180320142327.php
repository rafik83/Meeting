<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add link between AvailabilityTimeRange and Product
 */
class Version20180320142327 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE availability_time_ranges_products (availability_time_range_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_7A0FF57697CA18EB (availability_time_range_id), INDEX IDX_7A0FF5764584665A (product_id), PRIMARY KEY(availability_time_range_id, product_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE availability_time_ranges_products ADD CONSTRAINT FK_7A0FF57697CA18EB FOREIGN KEY (availability_time_range_id) REFERENCES availability_time_range (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE availability_time_ranges_products ADD CONSTRAINT FK_7A0FF5764584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE availability_time_ranges_products');
    }
}
