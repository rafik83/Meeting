<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add Transactional Mail Message and Message Translation
 */
class Version20180928140315 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE transactional_mail_message (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_FC2C43C371F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transactional_mail_message_associated_type (message_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_A7B793E7537A1329 (message_id), INDEX IDX_A7B793E7C54C8C93 (type_id), PRIMARY KEY(message_id, type_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transactional_mail_message_translation (id INT AUTO_INCREMENT NOT NULL, message_id INT NOT NULL, subject VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_A7E7514F537A1329 (message_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transactional_mail_message ADD CONSTRAINT FK_FC2C43C371F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transactional_mail_message_associated_type ADD CONSTRAINT FK_A7B793E7537A1329 FOREIGN KEY (message_id) REFERENCES transactional_mail_message (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transactional_mail_message_associated_type ADD CONSTRAINT FK_A7B793E7C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transactional_mail_message_translation ADD CONSTRAINT FK_A7E7514F537A1329 FOREIGN KEY (message_id) REFERENCES transactional_mail_message (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE transactional_mail_message_associated_type DROP FOREIGN KEY FK_A7B793E7537A1329');
        $this->addSql('ALTER TABLE transactional_mail_message_translation DROP FOREIGN KEY FK_A7E7514F537A1329');
        $this->addSql('DROP TABLE transactional_mail_message');
        $this->addSql('DROP TABLE transactional_mail_message_associated_type');
        $this->addSql('DROP TABLE transactional_mail_message_translation');
    }
}
