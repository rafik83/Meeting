<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Reverse Meeting / Request owner side oneToOne relation
 */
class Version20170113094502 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('SET foreign_key_checks = 0');
        $this->addSql('DROP TABLE meeting_request');
        $this->addSql('DROP TABLE meeting');
        $this->addSql('CREATE TABLE meeting_request (id INT AUTO_INCREMENT NOT NULL, from_id INT DEFAULT NULL, to_id INT DEFAULT NULL, creator_id INT DEFAULT NULL, state VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, state_updated_at DATETIME NOT NULL, INDEX IDX_A345C71278CED90B (from_id), INDEX IDX_A345C71230354A65 (to_id), INDEX IDX_A345C71261220EA6 (creator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meeting (id INT AUTO_INCREMENT NOT NULL, request_id INT NOT NULL, slot_id INT DEFAULT NULL, from_sheet_id INT DEFAULT NULL, to_sheet_id INT DEFAULT NULL, spot_id INT DEFAULT NULL, state VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_F515E139427EB8A5 (request_id), INDEX IDX_F515E13959E5119C (slot_id), INDEX IDX_F515E139CDFDE63F (from_sheet_id), INDEX IDX_F515E139464B980B (to_sheet_id), INDEX IDX_F515E1392DF1D37C (spot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE meeting_request ADD CONSTRAINT FK_A345C71278CED90B FOREIGN KEY (from_id) REFERENCES sheet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_request ADD CONSTRAINT FK_A345C71230354A65 FOREIGN KEY (to_id) REFERENCES sheet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_request ADD CONSTRAINT FK_A345C71261220EA6 FOREIGN KEY (creator_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT FK_F515E139427EB8A5 FOREIGN KEY (request_id) REFERENCES meeting_request (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT FK_F515E13959E5119C FOREIGN KEY (slot_id) REFERENCES meeting_slot (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT FK_F515E139CDFDE63F FOREIGN KEY (from_sheet_id) REFERENCES sheet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT FK_F515E139464B980B FOREIGN KEY (to_sheet_id) REFERENCES sheet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT FK_F515E1392DF1D37C FOREIGN KEY (spot_id) REFERENCES spot (id) ON DELETE CASCADE');
        $this->addSql('SET foreign_key_checks = 1');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // nothing to do
    }
}
