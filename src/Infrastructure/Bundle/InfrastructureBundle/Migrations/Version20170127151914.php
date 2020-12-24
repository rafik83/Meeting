<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170127151914 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE mass_unavailability_timeslot (id INT AUTO_INCREMENT NOT NULL, mass_id INT DEFAULT NULL, from_date DATETIME NOT NULL, to_date DATETIME NOT NULL, INDEX IDX_4190D3EDEA5DA7EB (mass_id), UNIQUE INDEX date_unique_idx (mass_id, from_date, to_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mass_unavailability_timeslot ADD CONSTRAINT FK_4190D3EDEA5DA7EB FOREIGN KEY (mass_id) REFERENCES mass_unavailability (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE mass_unavailability_timeslot');
    }
}
