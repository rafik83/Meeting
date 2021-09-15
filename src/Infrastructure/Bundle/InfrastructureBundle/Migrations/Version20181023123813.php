<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add 'messaging_campaign_user' table
 */
class Version20181023123813 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE messaging_campaign_user (campaign_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_4E427F89F639F774 (campaign_id), INDEX IDX_4E427F89A76ED395 (user_id), PRIMARY KEY(campaign_id, user_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE messaging_campaign_user ADD CONSTRAINT FK_4E427F89F639F774 FOREIGN KEY (campaign_id) REFERENCES messaging_campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE messaging_campaign_user ADD CONSTRAINT FK_4E427F89A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE messaging_campaign_user');
    }
}
