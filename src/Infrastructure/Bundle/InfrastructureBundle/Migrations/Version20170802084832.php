<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Foreign key not null on video_conference
 */
class Version20170802084832 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE video_conference CHANGE meeting_id meeting_id INT NOT NULL');
        $this->addSql('ALTER TABLE video_conference_token DROP FOREIGN KEY FK_F8A970559D1C3019');
        $this->addSql('DROP INDEX idx_f8a970559d1c3019 ON video_conference_token');
        $this->addSql('CREATE INDEX IDX_F8A97055A76ED395 ON video_conference_token (user_id)');
        $this->addSql('ALTER TABLE video_conference_token ADD CONSTRAINT FK_F8A970559D1C3019 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE video_conference CHANGE meeting_id meeting_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE video_conference_token DROP FOREIGN KEY FK_F8A97055A76ED395');
        $this->addSql('DROP INDEX idx_f8a97055a76ed395 ON video_conference_token');
        $this->addSql('CREATE INDEX IDX_F8A970559D1C3019 ON video_conference_token (user_id)');
        $this->addSql('ALTER TABLE video_conference_token ADD CONSTRAINT FK_F8A97055A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }
}
