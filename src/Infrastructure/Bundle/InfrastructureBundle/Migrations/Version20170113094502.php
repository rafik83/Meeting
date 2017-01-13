<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Reverse Meeting / Request owner side oneToOne relation
 */
class Version20170113094502 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE meeting_request DROP FOREIGN KEY FK_A345C71267433D9C');
        $this->addSql('DROP INDEX UNIQ_A345C71267433D9C ON meeting_request');
        $this->addSql('ALTER TABLE meeting_request DROP meeting_id');
        $this->addSql('ALTER TABLE meeting ADD request_id INT NOT NULL');
        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT FK_F515E139427EB8A5 FOREIGN KEY (request_id) REFERENCES meeting_request (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F515E139427EB8A5 ON meeting (request_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE meeting DROP FOREIGN KEY FK_F515E139427EB8A5');
        $this->addSql('DROP INDEX UNIQ_F515E139427EB8A5 ON meeting');
        $this->addSql('ALTER TABLE meeting DROP request_id');
        $this->addSql('ALTER TABLE meeting_request ADD meeting_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE meeting_request ADD CONSTRAINT FK_A345C71267433D9C FOREIGN KEY (meeting_id) REFERENCES meeting (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A345C71267433D9C ON meeting_request (meeting_id)');
    }
}
