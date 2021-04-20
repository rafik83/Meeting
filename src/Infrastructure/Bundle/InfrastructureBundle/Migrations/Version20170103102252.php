<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170103102252 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE messaging_campaign_sheet (campaign_id INT NOT NULL, sheet_id INT NOT NULL, INDEX IDX_1C9B82FBF639F774 (campaign_id), INDEX IDX_1C9B82FB8B1206A5 (sheet_id), PRIMARY KEY(campaign_id, sheet_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE messaging_campaign_sheet ADD CONSTRAINT FK_1C9B82FBF639F774 FOREIGN KEY (campaign_id) REFERENCES messaging_campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE messaging_campaign_sheet ADD CONSTRAINT FK_1C9B82FB8B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE messaging_campaign ADD filters LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE messaging_campaign_sheet');
        $this->addSql('ALTER TABLE messaging_campaign DROP filters');
    }
}
