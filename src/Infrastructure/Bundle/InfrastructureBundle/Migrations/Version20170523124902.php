<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add UserEventToken table
 */
class Version20170523124902 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE user_event_token (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, event_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, confirmed TINYINT(1) DEFAULT \'0\' NOT NULL, confirmed_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_B737D9825F37A13B (token), INDEX IDX_B737D982A76ED395 (user_id), INDEX IDX_B737D98271F7E88B (event_id),  UNIQUE INDEX user_event_token_unique_idx (event_id, user_id, type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_event_token ADD CONSTRAINT FK_B737D982A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_event_token ADD CONSTRAINT FK_B737D98271F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE user_event_token');
    }
}
