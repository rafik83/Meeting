<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add update or delete reason message on meetings
 */
class Version20190527082026 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE meeting_request ADD update_or_delete_reason_message_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE meeting_request ADD CONSTRAINT FK_A345C712C0D7323D FOREIGN KEY (update_or_delete_reason_message_id) REFERENCES meeting_message (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_A345C712C0D7323D ON meeting_request (update_or_delete_reason_message_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE meeting_request DROP FOREIGN KEY FK_A345C712C0D7323D');
        $this->addSql('DROP INDEX IDX_A345C712C0D7323D ON meeting_request');
        $this->addSql('ALTER TABLE meeting_request DROP update_or_delete_reason_message_id');
    }
}
