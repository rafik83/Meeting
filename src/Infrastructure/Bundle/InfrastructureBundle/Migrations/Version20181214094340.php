<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add rooming_accommodation and rooming_accommodation_overnight_capacity
 */
class Version20181214094340 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE rooming_accommodation (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, INDEX IDX_6399BABB71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rooming_accommodation_overnight_capacity (id INT AUTO_INCREMENT NOT NULL, accommodation_id INT NOT NULL, date DATE NOT NULL, capacity INT NOT NULL, INDEX IDX_149CBDA88F3692CD (accommodation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rooming_accommodation ADD CONSTRAINT FK_6399BABB71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE rooming_accommodation_overnight_capacity ADD CONSTRAINT FK_149CBDA88F3692CD FOREIGN KEY (accommodation_id) REFERENCES rooming_accommodation (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE rooming_accommodation_overnight_capacity DROP FOREIGN KEY FK_149CBDA88F3692CD');
        $this->addSql('DROP TABLE rooming_accommodation');
        $this->addSql('DROP TABLE rooming_accommodation_overnight_capacity');
    }
}
