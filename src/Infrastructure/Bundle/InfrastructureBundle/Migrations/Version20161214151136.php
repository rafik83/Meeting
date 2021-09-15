<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * This migration is related to issue #697 : It remove the unused order relation in participant table
 */
class Version20161214151136 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B118D9F6D38');
        $this->addSql('DROP INDEX IDX_D79F6B118D9F6D38 ON participant');
        $this->addSql('ALTER TABLE participant DROP order_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE participant ADD order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B118D9F6D38 FOREIGN KEY (order_id) REFERENCES _order (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_D79F6B118D9F6D38 ON participant (order_id)');
    }
}
