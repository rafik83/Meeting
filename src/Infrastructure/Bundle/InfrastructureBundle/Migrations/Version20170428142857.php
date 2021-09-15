<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170428142857 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE trace ADD object_type VARCHAR(255), ADD object_id INT');
        $this->addSql('CREATE INDEX object_index ON trace (object_type, object_id)');
        $this->addSql('UPDATE trace SET object_type = SUBSTRING(object, 1, 5), object_id = SUBSTRING(object, 6)');
        $this->addSql('ALTER TABLE trace DROP object');
        $this->addSql('ALTER TABLE trace CHANGE object_type object_type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE trace CHANGE object_id object_id INT NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE trace ADD object VARCHAR(255)');
        $this->addSql('UPDATE trace SET object = CONCAT(object_type, object_id)');
        $this->addSql('DROP INDEX object_index ON trace');
        $this->addSql('ALTER TABLE trace DROP object_type, DROP object_id');
        $this->addSql('ALTER TABLE trace CHANGE object object VARCHAR(255) NOT NULL');
    }
}
