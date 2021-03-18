<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Warning: This migration is irreversible !
 * Create table messaging_message_translation for Message translation
 * Transfer `messaging_message.subject` and `messaging_message.content`on messaging_message_translation
 * Drop `messaging_message.subject` and `messaging_message.content`
 */
class Version20170407152027 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE messaging_message_translation (id INT AUTO_INCREMENT NOT NULL, message_id INT DEFAULT NULL, subject VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, locale VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_72DF2E88537A1329 (message_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE messaging_message_translation ADD CONSTRAINT FK_72DF2E88537A1329 FOREIGN KEY (message_id) REFERENCES messaging_message (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function postUp(Schema $schema)
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('event.id as eventId, event.locales as eventLocales')
            ->from('event', 'event');
        $events = $queryBuilder->execute()->fetchAll();

        foreach ($events as $parameters) {
            foreach (json_decode($parameters['eventLocales']) as $locale) {
                $this->connection->executeQuery(
                    sprintf(
                        "INSERT INTO messaging_message_translation (subject, content, message_id, locale, created_at)
                          SELECT subject, content, id, '%s', created_at
                          FROM messaging_message
                          WHERE messaging_message.event_id = %s",
                        $locale,
                        $parameters['eventId']
                    )
                );
            }
        }

        $this->write('Migrated datas from messaging_message to messaging_message_translation');
        $this->connection->executeQuery('ALTER TABLE messaging_message DROP subject, DROP content');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE messaging_message_translation');
        $this->addSql('ALTER TABLE messaging_message ADD subject VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD content LONGTEXT NOT NULL COLLATE utf8_unicode_ci');
    }
}
