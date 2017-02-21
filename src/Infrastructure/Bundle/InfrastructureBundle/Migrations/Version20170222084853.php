<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create `invoice` table
 */
class Version20170222084853 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, sheet_id INT DEFAULT NULL, total DOUBLE PRECISION NOT NULL, number VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_9065174471F7E88B (event_id), INDEX IDX_906517448B1206A5 (sheet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_9065174471F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517448B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event DROP configuration_analytics_code');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('ALTER TABLE event ADD configuration_analytics_code VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
