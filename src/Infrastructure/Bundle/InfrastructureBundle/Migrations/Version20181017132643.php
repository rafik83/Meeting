<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181017132643 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE static_formulation (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, `static_formulation_key` VARCHAR(255) NOT NULL, INDEX IDX_142B003A71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE static_formulation_type (static_formulation_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_20DCF941E3015C22 (static_formulation_id), INDEX IDX_20DCF941C54C8C93 (type_id), PRIMARY KEY(static_formulation_id, type_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE static_formulation_translation (id INT AUTO_INCREMENT NOT NULL, static_formulation_id INT DEFAULT NULL, locale VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, INDEX IDX_525BC2AEE3015C22 (static_formulation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE static_formulation ADD CONSTRAINT FK_142B003A71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE static_formulation_type ADD CONSTRAINT FK_20DCF941E3015C22 FOREIGN KEY (static_formulation_id) REFERENCES static_formulation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE static_formulation_type ADD CONSTRAINT FK_20DCF941C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE static_formulation_translation ADD CONSTRAINT FK_525BC2AEE3015C22 FOREIGN KEY (static_formulation_id) REFERENCES static_formulation (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE static_formulation_type DROP FOREIGN KEY FK_20DCF941E3015C22');
        $this->addSql('ALTER TABLE static_formulation_translation DROP FOREIGN KEY FK_525BC2AEE3015C22');
        $this->addSql('DROP TABLE static_formulation');
        $this->addSql('DROP TABLE static_formulation_type');
        $this->addSql('DROP TABLE static_formulation_translation');
    }
}
