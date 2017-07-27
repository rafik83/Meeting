<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add user to video_conference_token
 */
class Version20170727142544 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE video_conference_token ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE video_conference_token ADD CONSTRAINT FK_F8A970559D1C3019 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_F8A970559D1C3019 ON video_conference_token (user_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE video_conference_token DROP FOREIGN KEY FK_F8A970559D1C3019');
        $this->addSql('DROP INDEX IDX_F8A970559D1C3019 ON video_conference_token');
        $this->addSql('ALTER TABLE video_conference_token DROP user_id');
    }
}
