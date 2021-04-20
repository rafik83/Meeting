<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20161123171557 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sheet_completeness (id INT AUTO_INCREMENT NOT NULL, sheet_id INT DEFAULT NULL, locale VARCHAR(255) NOT NULL, completeness INT NOT NULL, INDEX IDX_B19299138B1206A5 (sheet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sheet_completeness ADD CONSTRAINT FK_B19299138B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event ADD logo_extension VARCHAR(255) DEFAULT NULL, ADD email_team VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA37BC4DC6');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA71F7E88B');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAE92F8F78');
        $this->addSql('DROP INDEX IDX_BF5476CA37BC4DC6 ON notification');
        $this->addSql('DROP INDEX IDX_BF5476CAE92F8F78 ON notification');
        $this->addSql('DROP INDEX IDX_BF5476CA71F7E88B ON notification');
        $this->addSql('ALTER TABLE notification ADD sheet_id INT DEFAULT NULL, DROP emitter_id, DROP event_id, DROP recipient_id, DROP message, DROP url, DROP created_at, DROP view, CHANGE action type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA8B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_BF5476CA8B1206A5 ON notification (sheet_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sheet_completeness');
        $this->addSql('ALTER TABLE event DROP logo_extension, DROP email_team');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA8B1206A5');
        $this->addSql('DROP INDEX IDX_BF5476CA8B1206A5 ON notification');
        $this->addSql('ALTER TABLE notification ADD event_id INT DEFAULT NULL, ADD recipient_id INT DEFAULT NULL, ADD message LONGTEXT NOT NULL COLLATE utf8_unicode_ci, ADD url LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, ADD created_at DATETIME NOT NULL, ADD view TINYINT(1) NOT NULL, CHANGE sheet_id emitter_id INT DEFAULT NULL, CHANGE type action VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA37BC4DC6 FOREIGN KEY (emitter_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAE92F8F78 FOREIGN KEY (recipient_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_BF5476CA37BC4DC6 ON notification (emitter_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CAE92F8F78 ON notification (recipient_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA71F7E88B ON notification (event_id)');
    }
}
