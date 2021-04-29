<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add link between happenings and products
 */
class Version20180403100034 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE happenings_products (happening_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_6A76B5D8B7B10E6D (happening_id), INDEX IDX_6A76B5D84584665A (product_id), PRIMARY KEY(happening_id, product_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE happenings_products ADD CONSTRAINT FK_6A76B5D8B7B10E6D FOREIGN KEY (happening_id) REFERENCES happening (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE happenings_products ADD CONSTRAINT FK_6A76B5D84584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE happenings_products');
    }
}
