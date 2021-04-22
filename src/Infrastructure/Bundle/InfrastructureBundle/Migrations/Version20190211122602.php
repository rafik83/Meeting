<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add product_id on order_promotion_code
 */
class Version20190211122602 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_promotion_code ADD product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE order_promotion_code ADD CONSTRAINT FK_A99F68B74584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_A99F68B74584665A ON order_promotion_code (product_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_promotion_code DROP FOREIGN KEY FK_A99F68B74584665A');
        $this->addSql('DROP INDEX IDX_A99F68B74584665A ON order_promotion_code');
        $this->addSql('ALTER TABLE order_promotion_code DROP product_id');
    }
}
