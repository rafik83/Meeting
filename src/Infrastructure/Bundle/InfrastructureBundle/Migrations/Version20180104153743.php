<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add participant_product_id on Participant and create cart_row_participant
 */
class Version20180104153743 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE cart_row_participant (cart_row_id INT NOT NULL, participant_id INT NOT NULL, INDEX IDX_EF46B0A98D260BAD (cart_row_id), INDEX IDX_EF46B0A99D1C3019 (participant_id), PRIMARY KEY(cart_row_id, participant_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cart_row_participant ADD CONSTRAINT FK_EF46B0A98D260BAD FOREIGN KEY (cart_row_id) REFERENCES cart_row (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cart_row_participant ADD CONSTRAINT FK_EF46B0A99D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participant ADD participant_product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B1179448044 FOREIGN KEY (participant_product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_D79F6B1179448044 ON participant (participant_product_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE cart_row_participant');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B1179448044');
        $this->addSql('DROP INDEX IDX_D79F6B1179448044 ON participant');
        $this->addSql('ALTER TABLE participant DROP participant_product_id');
    }
}
