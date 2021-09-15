<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add Rooming\Stay and User\Event\PresenceDate table
 */
class Version20181218100545 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE rooming_stay (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, accommodation_id INT NOT NULL, arrival DATETIME NOT NULL, departure DATETIME NOT NULL, room_type VARCHAR(255) NOT NULL, INDEX IDX_399A35A471F7E88B (event_id), INDEX IDX_399A35A48F3692CD (accommodation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rooming_stay_user (stay_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_6CE4270BFB3AF7D6 (stay_id), INDEX IDX_6CE4270BA76ED395 (user_id), PRIMARY KEY(stay_id, user_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_event_presence_date (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, event_id INT NOT NULL, arrival DATETIME DEFAULT NULL, departure DATETIME DEFAULT NULL, INDEX IDX_4FFDF0DDA76ED395 (user_id), INDEX IDX_4FFDF0DD71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rooming_stay ADD CONSTRAINT FK_399A35A471F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rooming_stay ADD CONSTRAINT FK_399A35A48F3692CD FOREIGN KEY (accommodation_id) REFERENCES rooming_accommodation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rooming_stay_user ADD CONSTRAINT FK_6CE4270BFB3AF7D6 FOREIGN KEY (stay_id) REFERENCES rooming_stay (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rooming_stay_user ADD CONSTRAINT FK_6CE4270BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_event_presence_date ADD CONSTRAINT FK_4FFDF0DDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_event_presence_date ADD CONSTRAINT FK_4FFDF0DD71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE rooming_stay_user DROP FOREIGN KEY FK_6CE4270BFB3AF7D6');
        $this->addSql('DROP TABLE rooming_stay');
        $this->addSql('DROP TABLE rooming_stay_user');
        $this->addSql('DROP TABLE user_event_presence_date');
    }
}
