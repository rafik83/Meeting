<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add tip_opened table
 */
class Version20190610112308 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tip_opened (tip_id INT NOT NULL, user_id INT NOT NULL, opened_at DATETIME NOT NULL, INDEX IDX_89B1F45D476C47F6 (tip_id), INDEX IDX_89B1F45DA76ED395 (user_id), PRIMARY KEY(tip_id, user_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tip_opened ADD CONSTRAINT FK_89B1F45D476C47F6 FOREIGN KEY (tip_id) REFERENCES tip (id)');
        $this->addSql('ALTER TABLE tip_opened ADD CONSTRAINT FK_89B1F45DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tip_opened');
    }
}
