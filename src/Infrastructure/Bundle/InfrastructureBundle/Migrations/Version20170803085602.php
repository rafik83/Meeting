<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add video_conference and video_conference_token table
 */
class Version20170803085602 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE video_conference (id INT AUTO_INCREMENT NOT NULL, meeting_id INT NOT NULL, session_id VARCHAR(255) NOT NULL, UNIQUE INDEX unique_meeting_idx (meeting_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_conference_token (id INT AUTO_INCREMENT NOT NULL, video_conference_id INT NOT NULL, user_id INT NOT NULL, token LONGTEXT NOT NULL, INDEX IDX_F8A970556B31AFB2 (video_conference_id), INDEX IDX_F8A97055A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE video_conference ADD CONSTRAINT FK_11B22B7E67433D9C FOREIGN KEY (meeting_id) REFERENCES meeting (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE video_conference_token ADD CONSTRAINT FK_F8A970556B31AFB2 FOREIGN KEY (video_conference_id) REFERENCES video_conference (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE video_conference_token ADD CONSTRAINT FK_F8A97055A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE video_conference_token DROP FOREIGN KEY FK_F8A970556B31AFB2');
        $this->addSql('DROP TABLE video_conference');
        $this->addSql('DROP TABLE video_conference_token');
    }
}
