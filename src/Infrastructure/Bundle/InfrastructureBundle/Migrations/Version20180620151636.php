<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add table Badge
 */
class Version20180620151636 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE badge (id INT AUTO_INCREMENT NOT NULL, type_id INT DEFAULT NULL, event_id INT NOT NULL, header VARCHAR(255) DEFAULT NULL, show_header TINYINT(1) NOT NULL, show_footer_type_or_category VARCHAR(255) DEFAULT \'type\' NOT NULL, footer_text_color VARCHAR(255) DEFAULT \'#ffffff\' NOT NULL, footer_color VARCHAR(255) DEFAULT \'#000000\' NOT NULL, show_sheet_title TINYINT(1) NOT NULL, show_position TINYINT(1) NOT NULL, show_first_name TINYINT(1) NOT NULL, show_last_name TINYINT(1) NOT NULL, show_qrcode TINYINT(1) NOT NULL, activated TINYINT(1) NOT NULL, conditioned TINYINT(1) NOT NULL, conditioned_by_package TINYINT(1) NOT NULL, conditioned_by_states LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', UNIQUE INDEX UNIQ_FEF0481DC54C8C93 (type_id), INDEX IDX_FEF0481D71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE badge ADD CONSTRAINT FK_FEF0481DC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE badge ADD CONSTRAINT FK_FEF0481D71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE badge');
    }
}
