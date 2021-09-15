<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160901114946 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE payment_notification (id INT AUTO_INCREMENT NOT NULL, gateway_name LONGTEXT NOT NULL, details LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, transaction_id INT DEFAULT NULL, number VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, client_email VARCHAR(255) DEFAULT NULL, client_id VARCHAR(255) DEFAULT NULL, total_amount INT DEFAULT NULL, currency_code VARCHAR(255) DEFAULT NULL, details LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', UNIQUE INDEX UNIQ_6D28840D2FC0CB0F (transaction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment_token (hash VARCHAR(255) NOT NULL, details LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', after_url LONGTEXT DEFAULT NULL, target_url LONGTEXT NOT NULL, gateway_name VARCHAR(255) NOT NULL, PRIMARY KEY(hash)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D2FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id)');
        $this->addSql('ALTER TABLE billing_info ADD gender VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE order_row ADD parent_row_id INT DEFAULT NULL, ADD `label` LONGTEXT DEFAULT NULL, CHANGE data data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE order_row ADD CONSTRAINT FK_C76BB9BBCCB1AA34 FOREIGN KEY (parent_row_id) REFERENCES order_row (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_C76BB9BBCCB1AA34 ON order_row (parent_row_id)');
        $this->addSql('ALTER TABLE _order ADD billing_info_gender VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product CHANGE updatable_until deletable_until DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE sheet ADD enable TINYINT(1) NOT NULL, ADD in_catalog TINYINT(1) NOT NULL, ADD in_catalog_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE sheet_template ADD preview LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE transaction ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_723705D1A76ED395 ON transaction (user_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE payment_notification');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE payment_token');
        $this->addSql('ALTER TABLE _order DROP billing_info_gender');
        $this->addSql('ALTER TABLE billing_info DROP gender');
        $this->addSql('ALTER TABLE order_row DROP FOREIGN KEY FK_C76BB9BBCCB1AA34');
        $this->addSql('DROP INDEX IDX_C76BB9BBCCB1AA34 ON order_row');
        $this->addSql('ALTER TABLE order_row DROP parent_row_id, DROP `label`, CHANGE data data LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE product CHANGE deletable_until updatable_until DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE sheet DROP enable, DROP in_catalog, DROP in_catalog_at');
        $this->addSql('ALTER TABLE sheet_template DROP preview');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1A76ED395');
        $this->addSql('DROP INDEX IDX_723705D1A76ED395 ON transaction');
        $this->addSql('ALTER TABLE transaction DROP user_id');
    }
}
