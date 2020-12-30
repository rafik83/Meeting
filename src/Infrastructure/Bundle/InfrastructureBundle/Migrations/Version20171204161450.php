<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add payment conditions on Type
 */
class Version20171204161450 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE type_payment_conditions (id INT AUTO_INCREMENT NOT NULL, type_id INT NOT NULL, allow_deposit TINYINT(1) NOT NULL, deposit_until DATETIME DEFAULT NULL, minimum_for_deposit DOUBLE PRECISION DEFAULT NULL, deposit INT DEFAULT NULL, payment_modes LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', UNIQUE INDEX UNIQ_A6F1F3ACC54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE type_payment_conditions ADD CONSTRAINT FK_A6F1F3ACC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE type_payment_conditions');
    }
}
