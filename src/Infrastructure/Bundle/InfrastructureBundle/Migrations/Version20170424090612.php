<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add on delete cascade on payment when transaction is deleted
 */
class Version20170424090612 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D2FC0CB0F');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D2FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D2FC0CB0F');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D2FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id) ON DELETE SET NULL');
    }
}
